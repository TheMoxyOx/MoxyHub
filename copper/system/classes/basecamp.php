<?php
/**
 * Base Database item
 * $Id$
 */

class Basecamp
{
	// a map of basecamp user ids to copper user ids.
	private $b2c_user_map = array();
	private $b2c_client_map = array();
	private $fallback_user = array();

	public $import_stats = array();

	public function __construct()
	{
		$this->import_stats = array(
			'firm_people' => 0,
			'clients' => 0,
			'contacts' => 0,
			'projects' => 0,
			'todos' => 0,
			'milestones' => 0,
		);
	}

	public function import($xml_file)
	{
		$this->b2c_user_map = array();
		set_time_limit(600);
		ignore_user_abort(1);

		// we parse with simplexml.
		$xml = simplexml_load_file($xml_file);

		// do it in a group. fasttter.
		DB::begin_transaction();
		$this->import_firm($xml);
		$this->import_clients($xml);
		$this->import_projects($xml);

		DB::commit_transaction();
	}

	public function import_firm($xml)
	{
		// make a dummy client so that we can file projects
		$c = new Client(null);
		$c->Name = $xml->firm->name . ' (Basecamp)';
		$c->Phone1 = $xml->firm->{'phone-number-office'};
		$c->FAX = $xml->firm->{'phone-number-fax'};
		$c->Address1 = $xml->firm->{'address-one'};
		$c->Address2 = $xml->firm->{'address-two'};
		$c->City = $xml->firm->city;
		$c->Country = $xml->firm->country;
		$c->State = $xml->firm->state;
		$c->URL = $xml->firm->{'web-address'};
		$c->commit();
		
		// store the id pair in the mapping for later.
		$this->b2c_client_map[(int) $xml->firm->id] = $c->ID;
		
		foreach($xml->firm->people->person as $person)
		{
			$cu = new CopperUser(null);
			$cu->Username = $person->{'first-name'} . '.' . $person->{'last-name'};
			// it'll never match because it wont be valid md5. so we don't really care.
			$cu->Password = Utils::random_str(24);
			$cu->Title = $person->{'title'};
			$cu->FirstName = $person->{'first-name'};
			$cu->LastName = $person->{'last-name'};
			$cu->EmailAddress = $person->{'email-address'};
			$cu->Phone1 = $person->{'phone-number-office'};
			$cu->Phone2 = $person->{'phone-number-home'};
			$cu->Phone3 = $person->{'phone-number-mobile'};
			$cu->Module = 'projects';
			$cu->Active = 1;
			$cu->IMType = $person->{'im-service'};
			$cu->IMAccount = $person->{'im-handle'};
			if ($cu->commit())
			{
				$this->import_stats['firm_people']++;
			}

			// store the id pair in the mapping for later.
			$this->b2c_user_map[(int) $person->id] = $cu->ID;
		}

	}
	
	public function import_clients($xml)
	{
		foreach($xml->clients->client as $client)
		{
			$c = new Client(null);
			$c->Name = $client->name;
			$c->Phone1 = $client->{'phone-number-office'};
			$c->FAX = $client->{'phone-number-fax'};
			$c->Address1 = $client->{'address-one'};
			$c->Address2 = $client->{'address-two'};
			$c->City = $client->city;
			$c->Country = $client->country;
			$c->State = $client->state;
			$c->URL = $client->{'web-address'};
			if ($c->commit())
			{
				$this->import_stats['clients']++;
			}
			
			// store the id pair in the mapping for later.
			$this->b2c_client_map[(int)$client->id] = $c->ID;
			
			$first = true;
			foreach($client->people->person as $person)
			{
				$con = new Contact(null);
				$con->ClientID = $c->ID;
				$con->FirstName = $person->{'first-name'};
				$con->LastName = $person->{'last-name'};
				$con->Title = $person->{'title'};
				$con->EmailAddress1 = $person->{'email-address'};
				$con->Phone1 = $person->{'phone-number-office'};
				$con->Phone2 = $person->{'phone-number-home'};
				$con->Phone3 = $person->{'phone-number-mobile'};
				
				if ($first)
				{
					$con->KeyContact = 1;
					$first = FALSE;
				} else {
					$con->KeyContact = 0;
				}

				if ($con->commit())
				{
					$this->import_stats['contacts']++;
				}
			}
		}
		
	}
	
	public function import_projects($xml)
	{
		foreach($xml->projects->project as $project)
		{
			$p = new Project(null);
			$p->ClientID = $this->b2c_client_map[(int)$project->company->id];
			$p->Name = $project->name;
			$p->Owner = CopperUser::current()->ID;
			$p->StartDate = DB::date(strtotime($project->{'created-on'}));
			$p->Colour = '#0099ff';
			if ($p->commit())
			{
				$this->import_stats['projects']++;
			}
			
			$seq = 0;
			$t = new Task(null);
			$t->Name = "Basecamp Posts";
			$t->Description = "Here are all the posts from basecamp. You will find them underneath this parent task.";
			$t->ProjectID = $p->ID;
			$t->StartDate = DB::date(strtotime($project->{'created-on'}));
			$t->Sequence = $seq++;
			$t->Owner = $this->get_user(null);
			$t->commit();
			
			foreach($project->posts->post as $post)
			{
				$t = new Task(null);
				$t->Sequence = $seq++;
				$t->Indent = 1;
				$t->ProjectID = $p->ID;
				$t->Owner = $this->get_user($post->{'author-id'});
				$t->Name = $post->title;
				$t->Description = $post->body;
				$t->StartDate = DB::date(strtotime($post->{'posted-on'}));
				$t->commit();
				
				foreach($post->comments->comment as $comment)
				{
					$tc = new TaskComment(null);
					$tc->TaskID = $t->ID;
					$tc->Body = $comment->body;
					$tc->Date = DB::date(strtotime($comment->{'created-at'}));
					$tc->UserID = $this->get_user($comment->{'author-id'});
					$tc->commit();
				}
			}

			$t = new Task(null);
			$t->Name = "Basecamp Todo Lists";
			$t->Description = "Here are all the todo lists from basecamp. You will find them underneath this parent task.";
			$t->ProjectID = $p->ID;
			$t->StartDate = DB::date(strtotime($project->{'created-on'}));
			$t->Sequence = $seq++;
			$t->Owner = $this->get_user(null);
			$t->commit();

			foreach($project->{'todo-lists'}->{'todo-list'} as $todolist)
			{
				$tl = new Task(null);
				$tl->Sequence = $seq++;
				$tl->Indent = 1;
				$tl->ProjectID = $p->ID;
				$tl->Owner = $this->get_user(null);
				$tl->Name = $todolist->name;
				$tl->Description = $todolist->description;
				$total = ((int) $todolist->{'completed-count'} + (int) $todolist->{'uncompleted-count'});
				if ($total > 0)
				{
					$tl->PercentComplete = ((int) $todolist->{'completed-count'} / ((int) $todolist->{'completed-count'} + (int) $todolist->{'uncompleted-count'})) * 100;
				} else {
					$tl->PercentComplete = 100; // we're optimists.
				}
				$tl->commit();
				
				foreach($todolist->{'todo-items'}->{'todo-item'} as $todo)
				{
					$td = new Task(null);
					$td->Sequence = $seq++;
					$td->Indent = 2;
					$td->ProjectID = $p->ID;
					$td->Owner = $this->get_user($todo->{'responsible-party-id'});
					$td->Name = $todo->content;
					$td->PercentComplete = (((string) $todo->{'completed'}) == 'true') ? 100 : 0;
					$td->StartDate = DB::date(strtotime($todo->{'created-at'}));
					$td->EndDate = DB::date(strtotime($todo->{'completed-at'}));
					if ($td->commit())
					{
						$this->import_stats['todos']++;
					}
					
					foreach($todo->comments->comment as $comment)
					{
						$tc = new TaskComment(null);
						$tc->TaskID = $td->ID;
						$tc->Body = $comment->body;
						$tc->Date = DB::date(strtotime($comment->{'created-at'}));
						$tc->UserID = $this->get_user($comment->{'author-id'});
						$tc->commit();
					}
				}
			}
			
			$t = new Task(null);
			$t->Name = "Basecamp Milestones";
			$t->Description = "Here are all the milestones from basecamp. You will find them underneath this parent task.";
			$t->ProjectID = $p->ID;
			$t->StartDate = DB::date(strtotime($project->{'created-on'}));
			$t->Sequence = $seq++;
			$t->Owner = $this->get_user(null);
			$t->commit();
			
			foreach($project->milestones->milestone as $milestone)
			{
				$t = new Task(null);
				$t->Sequence = $seq++;
				$t->Indent = 1;
				$t->ProjectID = $p->ID;
				$t->Owner = $this->get_user($milestone->{'creator-id'});
				$t->Name = $milestone->title;
				$t->StartDate = DB::date(strtotime($post->{'created-on'}));
				$t->EndDate = DB::date(strtotime($post->{'completed-on'}));
				if ($t->commit())
				{
					$this->import_stats['milestones']++;
				}
				
				foreach($milestone->comments->comment as $comment)
				{
					$tc = new TaskComment(null);
					$tc->TaskID = $t->ID;
					$tc->Body = $comment->body;
					$tc->Date = DB::date(strtotime($comment->{'created-at'}));
					$tc->UserID = $this->get_user($comment->{'author-id'});
					$tc->commit();
				}
			}
			
		}
		
	}
	
	public function get_user($id)
	{
		if ($this->fallback_user == null)
		{
			// first, we create a dummy local user to assign stuff to if they aren't a proper user.
			$dummy = new CopperUser(null);
			$dummy->Username = 'DummyBasecampPlaceholder';
			// it'll never match because it wont be valid md5. so we don't really care.
			$dummy->Password = Utils::random_str(24);
			$dummy->Title = $person->{'title'};
			$dummy->FirstName = 'Basecamp';
			$dummy->LastName = 'Placeholder';
			$dummy->Module = 'projects';
			$dummy->Active = 0;
			$dummy->commit();
			$this->fallback_user = $dummy;
		}

		$id = (int) $id;
		if (array_key_exists($id, $this->b2c_user_map))
		{
			return $this->b2c_user_map[$id];
		} else {
			return $this->fallback_user->ID;
		}
	}
	
}

