--
-- Table structure for table `sysAdminSettings`
--

DROP TABLE IF EXISTS `sysAdminSettings`;
CREATE TABLE IF NOT EXISTS `sysAdminSettings` (
  `ID` int(11) NOT NULL auto_increment,
  `Setting` varchar(255) default NULL,
  `Value` varchar(255) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `Setting` (`Setting`)
);

INSERT INTO `sysAdminSettings` VALUES (1, 'RecordsPerPage', '10');
INSERT INTO `sysAdminSettings` VALUES (2, 'CurrencySymbol', '$');
INSERT INTO `sysAdminSettings` VALUES (3, 'AutoID', '1');
INSERT INTO `sysAdminSettings` VALUES (4, 'IDStartValue', '00002');
INSERT INTO `sysAdminSettings` VALUES (5, 'TaskLogEdit', '1');
INSERT INTO `sysAdminSettings` VALUES (6, 'EmailOnUpdate', '0');
INSERT INTO `sysAdminSettings` VALUES (7, 'DaysBeforeTaskDue', '0');
INSERT INTO `sysAdminSettings` VALUES (8, 'DaysAfterTaskDue', '0');
INSERT INTO `sysAdminSettings` VALUES (9, 'CCTaskOwner', '0');
INSERT INTO `sysAdminSettings` VALUES (10, 'FirstLogin', '20061128083531');
INSERT INTO `sysAdminSettings` VALUES (11, 'UsersInContacts', '1');
INSERT INTO `sysAdminSettings` VALUES (12, 'ShowDependentTasks', '1');
INSERT INTO `sysAdminSettings` VALUES (13, 'DefaultLanguage', 'en');
INSERT INTO `sysAdminSettings` VALUES (14, 'DateFormat', '1');
INSERT INTO `sysAdminSettings` VALUES (15, 'Terms', '14');
INSERT INTO `sysAdminSettings` VALUES (16, 'HourlyRate', '100');
INSERT INTO `sysAdminSettings` VALUES (17, 'TimeZone', '27');
INSERT INTO `sysAdminSettings` VALUES (18, 'ConvertToDays', '1');
INSERT INTO `sysAdminSettings` VALUES (19, 'PrettyDateFormat', '1');
INSERT INTO `sysAdminSettings` VALUES (20, 'LogoFile', 'logo.gif');
INSERT INTO `sysAdminSettings` VALUES (21, 'HeaderBackgroundColour', '#00CCFF');
INSERT INTO `sysAdminSettings` VALUES (22, 'WeekStart', 'Monday');
INSERT INTO `sysAdminSettings` VALUES (23, 'DecimalPlaces', '2');
INSERT INTO `sysAdminSettings` VALUES (24, 'DecimalPoint', '.');
INSERT INTO `sysAdminSettings` VALUES (25, 'ThousandsSeparator', ',');
INSERT INTO `sysAdminSettings` VALUES (26, 'MoneyDecimalPlaces', '2');
INSERT INTO `sysAdminSettings` VALUES (27, 'MoneyDecimalPoint', '.');
INSERT INTO `sysAdminSettings` VALUES (28, 'MoneyThousandsSeparator', ',');
INSERT INTO `sysAdminSettings` VALUES (29, 'ResourceManagement', '1');
-- --------------------------------------------------------

--
-- Table structure for table `sysGroupPermissions`
--

DROP TABLE IF EXISTS `sysGroupPermissions`;
CREATE TABLE IF NOT EXISTS `sysGroupPermissions` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `GroupID` int(11) default NULL,
  `ObjectID` varchar(32) default NULL,
  `ItemID` int(11) default NULL,
  `AccessID` tinyint(1) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `idx_group` (`GroupID`),
  KEY `idx_object` (`ObjectID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `sysModules`
--

DROP TABLE IF EXISTS `sysModules`;
CREATE TABLE IF NOT EXISTS `sysModules` (
  `ID` int(11) NOT NULL auto_increment,
  `Class` varchar(32) default NULL,
  `Hash` varchar(32) default NULL,
  `MenuItem` tinyint(1) default '0',
  `IsPublic` tinyint(1) default '0',
  `Name` varchar(32) default NULL,
  `Order` int(3) default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
);


INSERT INTO `sysModules` VALUES (1, 'authorisation', '1673FAEF26B794910DA1F9FE10C92CA0', 0, 1, 'Authorisation', 0);
INSERT INTO `sysModules` VALUES (2, 'error', '1B8513DD11A85A9A515EE520CC06CA8A', 0, 1, 'Error', 0);
INSERT INTO `sysModules` VALUES (3, 'profile', 'AA2222DE8DB17A3441A75C92203FCE5A', 0, 1, 'Profile', 0);
INSERT INTO `sysModules` VALUES (4, 'clients', '26D3FABDA482E8016DF1D2B0874636AC', 1, 0, 'Clients', 2);
INSERT INTO `sysModules` VALUES (5, 'projects', '64A0AA99426C9CB96E9AD674F21FB80E', 1, 0, 'Projects', 3);
INSERT INTO `sysModules` VALUES (6, 'administration', '22145FA5DADD915218B6E6063F4D7CEC', 1, 0, 'Administration', 8);
INSERT INTO `sysModules` VALUES (7, 'springboard', '5F17E7524B9975BAF3608D903CDC366E', 1, 1, 'Springboard', 1);
INSERT INTO `sysModules` VALUES (8, 'contacts', 'E857668ABB04E275C22496F24D7EB381', 1, 1, 'Contacts', 6);
INSERT INTO `sysModules` VALUES (9, 'calendar', 'A0E7B2A565119C0A7EC3126A16016113', 1, 1, 'Calendar', 4);
INSERT INTO `sysModules` VALUES (10, 'files', '45B963397AA40D4A0063E0D85E4FE7A1', 1, 0, 'Files', 5);
INSERT INTO `sysModules` VALUES (11, 'reports', '26D3FABDA482E8016DF1D2B0874636AC', 1, 0, 'Reports', 7);
INSERT INTO `sysModules` VALUES (12, 'search', '26D3FABDA482E8016DF1D2B0874636AC', 0, 1, 'Search', 0);
INSERT INTO `sysModules` VALUES (13, 'budget', 'NULL', 1, 0, 'Budget Access', 0);


-- --------------------------------------------------------

--
-- Table structure for table `sysPriority`
--

DROP TABLE IF EXISTS `sysPriority`;
CREATE TABLE IF NOT EXISTS `sysPriority` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`ID`)
);

INSERT INTO `sysPriority` VALUES (1, 'Low');
INSERT INTO `sysPriority` VALUES (2, 'Normal');
INSERT INTO `sysPriority` VALUES (3, 'High');
-- --------------------------------------------------------

--
-- Table structure for table `sysSessions`
--

DROP TABLE IF EXISTS `sysSessions`;
CREATE TABLE IF NOT EXISTS `sysSessions` (
  `ID` varchar(32) NOT NULL default '',
  `Timeout` int(10) default NULL,
  `Data` text,
  PRIMARY KEY  (`ID`),
  KEY `idx_timeout` (`Timeout`)
);

-- --------------------------------------------------------

--
-- Table structure for table `sysSettings`
--

DROP TABLE IF EXISTS `sysSettings`;
-- --------------------------------------------------------

--
-- Table structure for table `sysTimeZones`
--

DROP TABLE IF EXISTS `sysTimeZones`;
CREATE TABLE IF NOT EXISTS `sysTimeZones` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `LatLong` varchar(15) NOT NULL default '',
  `Zone` tinytext NOT NULL,
  PRIMARY KEY  (`ID`)
);


INSERT INTO `sysTimeZones` VALUES (1, '+4230+00131', 'Europe/Andorra');
INSERT INTO `sysTimeZones` VALUES (2, '+2518+05518', 'Asia/Dubai');
INSERT INTO `sysTimeZones` VALUES (3, '+3431+06912', 'Asia/Kabul');
INSERT INTO `sysTimeZones` VALUES (4, '+1703-06148', 'America/Antigua');
INSERT INTO `sysTimeZones` VALUES (5, '+1812-06304', 'America/Anguilla');
INSERT INTO `sysTimeZones` VALUES (6, '+4120+01950', 'Europe/Tirane');
INSERT INTO `sysTimeZones` VALUES (7, '+4011+04430', 'Asia/Yerevan');
INSERT INTO `sysTimeZones` VALUES (8, '+1211-06900', 'America/Curacao');
INSERT INTO `sysTimeZones` VALUES (9, '-0848+01314', 'Africa/Luanda');
INSERT INTO `sysTimeZones` VALUES (10, '-7750+16636', 'Antarctica/McMurdo');
INSERT INTO `sysTimeZones` VALUES (11, '-9000+00000', 'Antarctica/South_Pole');
INSERT INTO `sysTimeZones` VALUES (12, '-6448-06406', 'Antarctica/Palmer');
INSERT INTO `sysTimeZones` VALUES (13, '-6736+06253', 'Antarctica/Mawson');
INSERT INTO `sysTimeZones` VALUES (14, '-6835+07758', 'Antarctica/Davis');
INSERT INTO `sysTimeZones` VALUES (15, '-6617+11031', 'Antarctica/Casey');
INSERT INTO `sysTimeZones` VALUES (16, '-6640+14001', 'Antarctica/DumontDUrville');
INSERT INTO `sysTimeZones` VALUES (17, '-3436-05827', 'America/Buenos_Aires');
INSERT INTO `sysTimeZones` VALUES (18, '-3257-06040', 'America/Rosario');
INSERT INTO `sysTimeZones` VALUES (19, '-3124-06411', 'America/Cordoba');
INSERT INTO `sysTimeZones` VALUES (20, '-2411-06518', 'America/Jujuy');
INSERT INTO `sysTimeZones` VALUES (21, '-2828-06547', 'America/Catamarca');
INSERT INTO `sysTimeZones` VALUES (22, '-3253-06849', 'America/Mendoza');
INSERT INTO `sysTimeZones` VALUES (23, '-1416-17042', 'Pacific/Pago_Pago');
INSERT INTO `sysTimeZones` VALUES (24, '+4813+01620', 'Europe/Vienna');
INSERT INTO `sysTimeZones` VALUES (25, '-3133+15905', 'Australia/Lord_Howe');
INSERT INTO `sysTimeZones` VALUES (26, '-4253+14719', 'Australia/Hobart');
INSERT INTO `sysTimeZones` VALUES (27, '-3749+14458', 'Australia/Melbourne');
INSERT INTO `sysTimeZones` VALUES (28, '-3352+15113', 'Australia/Sydney');
INSERT INTO `sysTimeZones` VALUES (29, '-3157+14127', 'Australia/Broken_Hill');
INSERT INTO `sysTimeZones` VALUES (30, '-2728+15302', 'Australia/Brisbane');
INSERT INTO `sysTimeZones` VALUES (31, '-2016+14900', 'Australia/Lindeman');
INSERT INTO `sysTimeZones` VALUES (32, '-3455+13835', 'Australia/Adelaide');
INSERT INTO `sysTimeZones` VALUES (33, '-1228+13050', 'Australia/Darwin');
INSERT INTO `sysTimeZones` VALUES (34, '-3157+11551', 'Australia/Perth');
INSERT INTO `sysTimeZones` VALUES (35, '+1230-06858', 'America/Aruba');
INSERT INTO `sysTimeZones` VALUES (36, '+4023+04951', 'Asia/Baku');
INSERT INTO `sysTimeZones` VALUES (37, '+4352+01825', 'Europe/Sarajevo');
INSERT INTO `sysTimeZones` VALUES (38, '+1306-05937', 'America/Barbados');
INSERT INTO `sysTimeZones` VALUES (39, '+2343+09025', 'Asia/Dacca');
INSERT INTO `sysTimeZones` VALUES (40, '+5050+00420', 'Europe/Brussels');
INSERT INTO `sysTimeZones` VALUES (41, '+1222-00131', 'Africa/Ouagadougou');
INSERT INTO `sysTimeZones` VALUES (42, '+4241+02319', 'Europe/Sofia');
INSERT INTO `sysTimeZones` VALUES (43, '+2623+05035', 'Asia/Bahrain');
INSERT INTO `sysTimeZones` VALUES (44, '-0323+02922', 'Africa/Bujumbura');
INSERT INTO `sysTimeZones` VALUES (45, '+0629+00237', 'Africa/Porto-Novo');
INSERT INTO `sysTimeZones` VALUES (46, '+3217-06446', 'Atlantic/Bermuda');
INSERT INTO `sysTimeZones` VALUES (47, '+0456+11455', 'Asia/Brunei');
INSERT INTO `sysTimeZones` VALUES (48, '-1630-06809', 'America/La_Paz');
INSERT INTO `sysTimeZones` VALUES (49, '-0351-03225', 'America/Noronha');
INSERT INTO `sysTimeZones` VALUES (50, '-0127-04829', 'America/Belem');
INSERT INTO `sysTimeZones` VALUES (51, '-0343-03830', 'America/Fortaleza');
INSERT INTO `sysTimeZones` VALUES (52, '-0712-04812', 'America/Araguaina');
INSERT INTO `sysTimeZones` VALUES (53, '-0940-03543', 'America/Maceio');
INSERT INTO `sysTimeZones` VALUES (54, '-2332-04637', 'America/Sao_Paulo');
INSERT INTO `sysTimeZones` VALUES (55, '-1535-05605', 'America/Cuiaba');
INSERT INTO `sysTimeZones` VALUES (56, '-0846-06354', 'America/Porto_Velho');
INSERT INTO `sysTimeZones` VALUES (57, '-0308-06001', 'America/Manaus');
INSERT INTO `sysTimeZones` VALUES (58, '-0934-06731', 'America/Porto_Acre');
INSERT INTO `sysTimeZones` VALUES (59, '+2505-07721', 'America/Nassau');
INSERT INTO `sysTimeZones` VALUES (60, '+2728+08939', 'Asia/Thimbu');
INSERT INTO `sysTimeZones` VALUES (61, '-2545+02555', 'Africa/Gaborone');
INSERT INTO `sysTimeZones` VALUES (62, '+5354+02734', 'Europe/Minsk');
INSERT INTO `sysTimeZones` VALUES (63, '+1730-08812', 'America/Belize');
INSERT INTO `sysTimeZones` VALUES (64, '+4734-05243', 'America/St_Johns');
INSERT INTO `sysTimeZones` VALUES (65, '+4439-06336', 'America/Halifax');
INSERT INTO `sysTimeZones` VALUES (66, '+4612-05957', 'America/Glace_Bay');
INSERT INTO `sysTimeZones` VALUES (67, '+5320-06025', 'America/Goose_Bay');
INSERT INTO `sysTimeZones` VALUES (68, '+6608-06544', 'America/Pangnirtung');
INSERT INTO `sysTimeZones` VALUES (69, '+4531-07334', 'America/Montreal');
INSERT INTO `sysTimeZones` VALUES (70, '+4901-08816', 'America/Nipigon');
INSERT INTO `sysTimeZones` VALUES (71, '+4823-08915', 'America/Thunder_Bay');
INSERT INTO `sysTimeZones` VALUES (72, '+6344-06828', 'America/Iqaluit');
INSERT INTO `sysTimeZones` VALUES (73, '+4953-09709', 'America/Winnipeg');
INSERT INTO `sysTimeZones` VALUES (74, '+4843-09429', 'America/Rainy_River');
INSERT INTO `sysTimeZones` VALUES (75, '+6245-09210', 'America/Rankin_Inlet');
INSERT INTO `sysTimeZones` VALUES (76, '+5024-10439', 'America/Regina');
INSERT INTO `sysTimeZones` VALUES (77, '+5017-10750', 'America/Swift_Current');
INSERT INTO `sysTimeZones` VALUES (78, '+5333-11328', 'America/Edmonton');
INSERT INTO `sysTimeZones` VALUES (79, '+6227-11421', 'America/Yellowknife');
INSERT INTO `sysTimeZones` VALUES (80, '+6825-11330', 'America/Inuvik');
INSERT INTO `sysTimeZones` VALUES (81, '+5946-12014', 'America/Dawson_Creek');
INSERT INTO `sysTimeZones` VALUES (82, '+4916-12307', 'America/Vancouver');
INSERT INTO `sysTimeZones` VALUES (83, '+6043-13503', 'America/Whitehorse');
INSERT INTO `sysTimeZones` VALUES (84, '+6404-13925', 'America/Dawson');
INSERT INTO `sysTimeZones` VALUES (85, '-1210+09655', 'Indian/Cocos');
INSERT INTO `sysTimeZones` VALUES (86, '-0418+01518', 'Africa/Kinshasa');
INSERT INTO `sysTimeZones` VALUES (87, '-1140+02728', 'Africa/Lubumbashi');
INSERT INTO `sysTimeZones` VALUES (88, '+0422+01835', 'Africa/Bangui');
INSERT INTO `sysTimeZones` VALUES (89, '-0416+01517', 'Africa/Brazzaville');
INSERT INTO `sysTimeZones` VALUES (90, '+4723+00832', 'Europe/Zurich');
INSERT INTO `sysTimeZones` VALUES (91, '+0519-00402', 'Africa/Abidjan');
INSERT INTO `sysTimeZones` VALUES (92, '-2114-15946', 'Pacific/Rarotonga');
INSERT INTO `sysTimeZones` VALUES (93, '-3327-07040', 'America/Santiago');
INSERT INTO `sysTimeZones` VALUES (94, '-2710-10927', 'Pacific/Easter');
INSERT INTO `sysTimeZones` VALUES (95, '+0403+00942', 'Africa/Douala');
INSERT INTO `sysTimeZones` VALUES (96, '+4545+12641', 'Asia/Harbin');
INSERT INTO `sysTimeZones` VALUES (97, '+3114+12128', 'Asia/Shanghai');
INSERT INTO `sysTimeZones` VALUES (98, '+2217+11409', 'Asia/Hong_Kong');
INSERT INTO `sysTimeZones` VALUES (99, '+2934+10635', 'Asia/Chungking');
INSERT INTO `sysTimeZones` VALUES (100, '+4348+08735', 'Asia/Urumqi');
INSERT INTO `sysTimeZones` VALUES (101, '+3929+07559', 'Asia/Kashgar');
INSERT INTO `sysTimeZones` VALUES (102, '+0436-07405', 'America/Bogota');
INSERT INTO `sysTimeZones` VALUES (103, '+0956-08405', 'America/Costa_Rica');
INSERT INTO `sysTimeZones` VALUES (104, '+2308-08222', 'America/Havana');
INSERT INTO `sysTimeZones` VALUES (105, '+1455-02331', 'Atlantic/Cape_Verde');
INSERT INTO `sysTimeZones` VALUES (106, '-1025+10543', 'Indian/Christmas');
INSERT INTO `sysTimeZones` VALUES (107, '+3510+03322', 'Asia/Nicosia');
INSERT INTO `sysTimeZones` VALUES (108, '+5005+01426', 'Europe/Prague');
INSERT INTO `sysTimeZones` VALUES (109, '+5230+01322', 'Europe/Berlin');
INSERT INTO `sysTimeZones` VALUES (110, '+1136+04309', 'Africa/Djibouti');
INSERT INTO `sysTimeZones` VALUES (111, '+5540+01235', 'Europe/Copenhagen');
INSERT INTO `sysTimeZones` VALUES (112, '+1518-06124', 'America/Dominica');
INSERT INTO `sysTimeZones` VALUES (113, '+1828-06954', 'America/Santo_Domingo');
INSERT INTO `sysTimeZones` VALUES (114, '+3647+00303', 'Africa/Algiers');
INSERT INTO `sysTimeZones` VALUES (115, '-0210-07950', 'America/Guayaquil');
INSERT INTO `sysTimeZones` VALUES (116, '-0054-08936', 'Pacific/Galapagos');
INSERT INTO `sysTimeZones` VALUES (117, '+5925+02445', 'Europe/Tallinn');
INSERT INTO `sysTimeZones` VALUES (118, '+3003+03115', 'Africa/Cairo');
INSERT INTO `sysTimeZones` VALUES (119, '+2709-01312', 'Africa/El_Aaiun');
INSERT INTO `sysTimeZones` VALUES (120, '+1520+03853', 'Africa/Asmera');
INSERT INTO `sysTimeZones` VALUES (121, '+4024-00341', 'Europe/Madrid');
INSERT INTO `sysTimeZones` VALUES (122, '+3553-00519', 'Africa/Ceuta');
INSERT INTO `sysTimeZones` VALUES (123, '+2806-01524', 'Atlantic/Canary');
INSERT INTO `sysTimeZones` VALUES (124, '+0902+03842', 'Africa/Addis_Ababa');
INSERT INTO `sysTimeZones` VALUES (125, '+6010+02458', 'Europe/Helsinki');
INSERT INTO `sysTimeZones` VALUES (126, '-1808+17825', 'Pacific/Fiji');
INSERT INTO `sysTimeZones` VALUES (127, '-5142-05751', 'Atlantic/Stanley');
INSERT INTO `sysTimeZones` VALUES (128, '+0931+13808', 'Pacific/Yap');
INSERT INTO `sysTimeZones` VALUES (129, '+0725+15147', 'Pacific/Truk');
INSERT INTO `sysTimeZones` VALUES (130, '+0658+15813', 'Pacific/Ponape');
INSERT INTO `sysTimeZones` VALUES (131, '+0519+16259', 'Pacific/Kosrae');
INSERT INTO `sysTimeZones` VALUES (132, '+6201-00646', 'Atlantic/Faeroe');
INSERT INTO `sysTimeZones` VALUES (133, '+4852+00220', 'Europe/Paris');
INSERT INTO `sysTimeZones` VALUES (134, '+0023+00927', 'Africa/Libreville');
INSERT INTO `sysTimeZones` VALUES (135, '+512830-0001845', 'Europe/London');
INSERT INTO `sysTimeZones` VALUES (136, '+5435-00555', 'Europe/Belfast');
INSERT INTO `sysTimeZones` VALUES (137, '+1203-06145', 'America/Grenada');
INSERT INTO `sysTimeZones` VALUES (138, '+4143+04449', 'Asia/Tbilisi');
INSERT INTO `sysTimeZones` VALUES (139, '+0456-05220', 'America/Cayenne');
INSERT INTO `sysTimeZones` VALUES (140, '+0533-00013', 'Africa/Accra');
INSERT INTO `sysTimeZones` VALUES (141, '+3608-00521', 'Europe/Gibraltar');
INSERT INTO `sysTimeZones` VALUES (142, '+7030-02215', 'America/Scoresbysund');
INSERT INTO `sysTimeZones` VALUES (143, '+6411-05144', 'America/Godthab');
INSERT INTO `sysTimeZones` VALUES (144, '+7634-06847', 'America/Thule');
INSERT INTO `sysTimeZones` VALUES (145, '+1328-01639', 'Africa/Banjul');
INSERT INTO `sysTimeZones` VALUES (146, '+0931-01343', 'Africa/Conakry');
INSERT INTO `sysTimeZones` VALUES (147, '+1614-06132', 'America/Guadeloupe');
INSERT INTO `sysTimeZones` VALUES (148, '+0345+00847', 'Africa/Malabo');
INSERT INTO `sysTimeZones` VALUES (149, '+3758+02343', 'Europe/Athens');
INSERT INTO `sysTimeZones` VALUES (150, '-5416-03632', 'Atlantic/South_Georgia');
INSERT INTO `sysTimeZones` VALUES (151, '+1438-09031', 'America/Guatemala');
INSERT INTO `sysTimeZones` VALUES (152, '+1328+14445', 'Pacific/Guam');
INSERT INTO `sysTimeZones` VALUES (153, '+1151-01535', 'Africa/Bissau');
INSERT INTO `sysTimeZones` VALUES (154, '+0648-05810', 'America/Guyana');
INSERT INTO `sysTimeZones` VALUES (155, '+1406-08713', 'America/Tegucigalpa');
INSERT INTO `sysTimeZones` VALUES (156, '+4548+01558', 'Europe/Zagreb');
INSERT INTO `sysTimeZones` VALUES (157, '+1832-07220', 'America/Port-au-Prince');
INSERT INTO `sysTimeZones` VALUES (158, '+4730+01905', 'Europe/Budapest');
INSERT INTO `sysTimeZones` VALUES (159, '-0610+10648', 'Asia/Jakarta');
INSERT INTO `sysTimeZones` VALUES (160, '-0507+11924', 'Asia/Ujung_Pandang');
INSERT INTO `sysTimeZones` VALUES (161, '-0232+14042', 'Asia/Jayapura');
INSERT INTO `sysTimeZones` VALUES (162, '+5320-00615', 'Europe/Dublin');
INSERT INTO `sysTimeZones` VALUES (163, '+3146+03514', 'Asia/Jerusalem');
INSERT INTO `sysTimeZones` VALUES (164, '+2232+08822', 'Asia/Calcutta');
INSERT INTO `sysTimeZones` VALUES (165, '-0720+07225', 'Indian/Chagos');
INSERT INTO `sysTimeZones` VALUES (166, '+3321+04425', 'Asia/Baghdad');
INSERT INTO `sysTimeZones` VALUES (167, '+3540+05126', 'Asia/Tehran');
INSERT INTO `sysTimeZones` VALUES (168, '+6409-02151', 'Atlantic/Reykjavik');
INSERT INTO `sysTimeZones` VALUES (169, '+4154+01229', 'Europe/Rome');
INSERT INTO `sysTimeZones` VALUES (170, '+1800-07648', 'America/Jamaica');
INSERT INTO `sysTimeZones` VALUES (171, '+3157+03556', 'Asia/Amman');
INSERT INTO `sysTimeZones` VALUES (172, '+353916+1394441', 'Asia/Tokyo');
INSERT INTO `sysTimeZones` VALUES (173, '-0117+03649', 'Africa/Nairobi');
INSERT INTO `sysTimeZones` VALUES (174, '+4254+07436', 'Asia/Bishkek');
INSERT INTO `sysTimeZones` VALUES (175, '+1133+10455', 'Asia/Phnom_Penh');
INSERT INTO `sysTimeZones` VALUES (176, '+0125+17300', 'Pacific/Tarawa');
INSERT INTO `sysTimeZones` VALUES (177, '-0308-17105', 'Pacific/Enderbury');
INSERT INTO `sysTimeZones` VALUES (178, '+0152-15720', 'Pacific/Kiritimati');
INSERT INTO `sysTimeZones` VALUES (179, '-1141+04316', 'Indian/Comoro');
INSERT INTO `sysTimeZones` VALUES (180, '+1718-06243', 'America/St_Kitts');
INSERT INTO `sysTimeZones` VALUES (181, '+3901+12545', 'Asia/Pyongyang');
INSERT INTO `sysTimeZones` VALUES (182, '+3733+12658', 'Asia/Seoul');
INSERT INTO `sysTimeZones` VALUES (183, '+2920+04759', 'Asia/Kuwait');
INSERT INTO `sysTimeZones` VALUES (184, '+1918-08123', 'America/Cayman');
INSERT INTO `sysTimeZones` VALUES (185, '+4315+07657', 'Asia/Almaty');
INSERT INTO `sysTimeZones` VALUES (186, '+5017+05710', 'Asia/Aqtobe');
INSERT INTO `sysTimeZones` VALUES (187, '+4431+05016', 'Asia/Aqtau');
INSERT INTO `sysTimeZones` VALUES (188, '+1758+10236', 'Asia/Vientiane');
INSERT INTO `sysTimeZones` VALUES (189, '+3353+03530', 'Asia/Beirut');
INSERT INTO `sysTimeZones` VALUES (190, '+1401-06100', 'America/St_Lucia');
INSERT INTO `sysTimeZones` VALUES (191, '+4709+00931', 'Europe/Vaduz');
INSERT INTO `sysTimeZones` VALUES (192, '+0656+07951', 'Asia/Colombo');
INSERT INTO `sysTimeZones` VALUES (193, '+0618-01047', 'Africa/Monrovia');
INSERT INTO `sysTimeZones` VALUES (194, '-2928+02730', 'Africa/Maseru');
INSERT INTO `sysTimeZones` VALUES (195, '+5441+02519', 'Europe/Vilnius');
INSERT INTO `sysTimeZones` VALUES (196, '+4936+00609', 'Europe/Luxembourg');
INSERT INTO `sysTimeZones` VALUES (197, '+5657+02406', 'Europe/Riga');
INSERT INTO `sysTimeZones` VALUES (198, '+3254+01311', 'Africa/Tripoli');
INSERT INTO `sysTimeZones` VALUES (199, '+3339-00735', 'Africa/Casablanca');
INSERT INTO `sysTimeZones` VALUES (200, '+4342+00723', 'Europe/Monaco');
INSERT INTO `sysTimeZones` VALUES (201, '+4700+02850', 'Europe/Chisinau');
INSERT INTO `sysTimeZones` VALUES (202, '-1855+04731', 'Indian/Antananarivo');
INSERT INTO `sysTimeZones` VALUES (203, '+0709+17112', 'Pacific/Majuro');
INSERT INTO `sysTimeZones` VALUES (204, '+0905+16720', 'Pacific/Kwajalein');
INSERT INTO `sysTimeZones` VALUES (205, '+4159+02126', 'Europe/Skopje');
INSERT INTO `sysTimeZones` VALUES (206, '+1239-00800', 'Africa/Bamako');
INSERT INTO `sysTimeZones` VALUES (207, '+1446-00301', 'Africa/Timbuktu');
INSERT INTO `sysTimeZones` VALUES (208, '+1647+09610', 'Asia/Rangoon');
INSERT INTO `sysTimeZones` VALUES (209, '+4755+10653', 'Asia/Ulan_Bator');
INSERT INTO `sysTimeZones` VALUES (210, '+2214+11335', 'Asia/Macao');
INSERT INTO `sysTimeZones` VALUES (211, '+1512+14545', 'Pacific/Saipan');
INSERT INTO `sysTimeZones` VALUES (212, '+1436-06105', 'America/Martinique');
INSERT INTO `sysTimeZones` VALUES (213, '+1806-01557', 'Africa/Nouakchott');
INSERT INTO `sysTimeZones` VALUES (214, '+1644-06213', 'America/Montserrat');
INSERT INTO `sysTimeZones` VALUES (215, '+3554+01431', 'Europe/Malta');
INSERT INTO `sysTimeZones` VALUES (216, '-2010+05730', 'Indian/Mauritius');
INSERT INTO `sysTimeZones` VALUES (217, '+0410+07330', 'Indian/Maldives');
INSERT INTO `sysTimeZones` VALUES (218, '-1547+03500', 'Africa/Blantyre');
INSERT INTO `sysTimeZones` VALUES (219, '+2105-08646', 'America/Cancun');
INSERT INTO `sysTimeZones` VALUES (220, '+1924-09909', 'America/Mexico_City');
INSERT INTO `sysTimeZones` VALUES (221, '+2313-10625', 'America/Mazatlan');
INSERT INTO `sysTimeZones` VALUES (222, '+2838-10605', 'America/Chihuahua');
INSERT INTO `sysTimeZones` VALUES (223, '+3152-11637', 'America/Ensenada');
INSERT INTO `sysTimeZones` VALUES (224, '+3232-11701', 'America/Tijuana');
INSERT INTO `sysTimeZones` VALUES (225, '+0310+10142', 'Asia/Kuala_Lumpur');
INSERT INTO `sysTimeZones` VALUES (226, '+0133+11020', 'Asia/Kuching');
INSERT INTO `sysTimeZones` VALUES (227, '-2558+03235', 'Africa/Maputo');
INSERT INTO `sysTimeZones` VALUES (228, '-2234+01706', 'Africa/Windhoek');
INSERT INTO `sysTimeZones` VALUES (229, '-2216+16530', 'Pacific/Noumea');
INSERT INTO `sysTimeZones` VALUES (230, '+1331+00207', 'Africa/Niamey');
INSERT INTO `sysTimeZones` VALUES (231, '-2903+16758', 'Pacific/Norfolk');
INSERT INTO `sysTimeZones` VALUES (232, '+0627+00324', 'Africa/Lagos');
INSERT INTO `sysTimeZones` VALUES (233, '+1209-08617', 'America/Managua');
INSERT INTO `sysTimeZones` VALUES (234, '+5222+00454', 'Europe/Amsterdam');
INSERT INTO `sysTimeZones` VALUES (235, '+5955+01045', 'Europe/Oslo');
INSERT INTO `sysTimeZones` VALUES (236, '+2743+08519', 'Asia/Katmandu');
INSERT INTO `sysTimeZones` VALUES (237, '-0031+16655', 'Pacific/Nauru');
INSERT INTO `sysTimeZones` VALUES (238, '-1901+16955', 'Pacific/Niue');
INSERT INTO `sysTimeZones` VALUES (239, '-3652+17446', 'Pacific/Auckland');
INSERT INTO `sysTimeZones` VALUES (240, '-4355-17630', 'Pacific/Chatham');
INSERT INTO `sysTimeZones` VALUES (241, '+2336+05835', 'Asia/Muscat');
INSERT INTO `sysTimeZones` VALUES (242, '+0858-07932', 'America/Panama');
INSERT INTO `sysTimeZones` VALUES (243, '-1203-07703', 'America/Lima');
INSERT INTO `sysTimeZones` VALUES (244, '-1732-14934', 'Pacific/Tahiti');
INSERT INTO `sysTimeZones` VALUES (245, '-0900-13930', 'Pacific/Marquesas');
INSERT INTO `sysTimeZones` VALUES (246, '-2308-13457', 'Pacific/Gambier');
INSERT INTO `sysTimeZones` VALUES (247, '-0930+14710', 'Pacific/Port_Moresby');
INSERT INTO `sysTimeZones` VALUES (248, '+1435+12100', 'Asia/Manila');
INSERT INTO `sysTimeZones` VALUES (249, '+2452+06703', 'Asia/Karachi');
INSERT INTO `sysTimeZones` VALUES (250, '+5215+02100', 'Europe/Warsaw');
INSERT INTO `sysTimeZones` VALUES (251, '+4703-05620', 'America/Miquelon');
INSERT INTO `sysTimeZones` VALUES (252, '-2504-13005', 'Pacific/Pitcairn');
INSERT INTO `sysTimeZones` VALUES (253, '+182806-0660622', 'America/Puerto_Rico');
INSERT INTO `sysTimeZones` VALUES (254, '+3130+03428', 'Asia/Gaza');
INSERT INTO `sysTimeZones` VALUES (255, '+3843-00908', 'Europe/Lisbon');
INSERT INTO `sysTimeZones` VALUES (256, '+3238-01654', 'Atlantic/Madeira');
INSERT INTO `sysTimeZones` VALUES (257, '+3744-02540', 'Atlantic/Azores');
INSERT INTO `sysTimeZones` VALUES (258, '+0720+13429', 'Pacific/Palau');
INSERT INTO `sysTimeZones` VALUES (259, '-2516-05740', 'America/Asuncion');
INSERT INTO `sysTimeZones` VALUES (260, '+2517+05132', 'Asia/Qatar');
INSERT INTO `sysTimeZones` VALUES (261, '-2052+05528', 'Indian/Reunion');
INSERT INTO `sysTimeZones` VALUES (262, '+4426+02606', 'Europe/Bucharest');
INSERT INTO `sysTimeZones` VALUES (263, '+5443+02030', 'Europe/Kaliningrad');
INSERT INTO `sysTimeZones` VALUES (264, '+5545+03735', 'Europe/Moscow');
INSERT INTO `sysTimeZones` VALUES (265, '+5312+05009', 'Europe/Samara');
INSERT INTO `sysTimeZones` VALUES (266, '+5651+06036', 'Asia/Yekaterinburg');
INSERT INTO `sysTimeZones` VALUES (267, '+5500+07324', 'Asia/Omsk');
INSERT INTO `sysTimeZones` VALUES (268, '+5502+08255', 'Asia/Novosibirsk');
INSERT INTO `sysTimeZones` VALUES (269, '+5601+09250', 'Asia/Krasnoyarsk');
INSERT INTO `sysTimeZones` VALUES (270, '+5216+10420', 'Asia/Irkutsk');
INSERT INTO `sysTimeZones` VALUES (271, '+6200+12940', 'Asia/Yakutsk');
INSERT INTO `sysTimeZones` VALUES (272, '+4310+13156', 'Asia/Vladivostok');
INSERT INTO `sysTimeZones` VALUES (273, '+5934+15048', 'Asia/Magadan');
INSERT INTO `sysTimeZones` VALUES (274, '+5301+15839', 'Asia/Kamchatka');
INSERT INTO `sysTimeZones` VALUES (275, '+6445+17729', 'Asia/Anadyr');
INSERT INTO `sysTimeZones` VALUES (276, '-0157+03004', 'Africa/Kigali');
INSERT INTO `sysTimeZones` VALUES (277, '+2438+04643', 'Asia/Riyadh');
INSERT INTO `sysTimeZones` VALUES (278, '-0932+16012', 'Pacific/Guadalcanal');
INSERT INTO `sysTimeZones` VALUES (279, '-0440+05528', 'Indian/Mahe');
INSERT INTO `sysTimeZones` VALUES (280, '+1536+03232', 'Africa/Khartoum');
INSERT INTO `sysTimeZones` VALUES (281, '+5920+01803', 'Europe/Stockholm');
INSERT INTO `sysTimeZones` VALUES (282, '+0117+10351', 'Asia/Singapore');
INSERT INTO `sysTimeZones` VALUES (283, '-1555-00542', 'Atlantic/St_Helena');
INSERT INTO `sysTimeZones` VALUES (284, '+4603+01431', 'Europe/Ljubljana');
INSERT INTO `sysTimeZones` VALUES (285, '+7800+01600', 'Arctic/Longyearbyen');
INSERT INTO `sysTimeZones` VALUES (286, '+7059-00805', 'Atlantic/Jan_Mayen');
INSERT INTO `sysTimeZones` VALUES (287, '+4809+01707', 'Europe/Bratislava');
INSERT INTO `sysTimeZones` VALUES (288, '+0830-01315', 'Africa/Freetown');
INSERT INTO `sysTimeZones` VALUES (289, '+4355+01228', 'Europe/San_Marino');
INSERT INTO `sysTimeZones` VALUES (290, '+1440-01726', 'Africa/Dakar');
INSERT INTO `sysTimeZones` VALUES (291, '+0204+04522', 'Africa/Mogadishu');
INSERT INTO `sysTimeZones` VALUES (292, '+0550-05510', 'America/Paramaribo');
INSERT INTO `sysTimeZones` VALUES (293, '+0020+00644', 'Africa/Sao_Tome');
INSERT INTO `sysTimeZones` VALUES (294, '+1342-08912', 'America/El_Salvador');
INSERT INTO `sysTimeZones` VALUES (295, '+3330+03618', 'Asia/Damascus');
INSERT INTO `sysTimeZones` VALUES (296, '-2618+03106', 'Africa/Mbabane');
INSERT INTO `sysTimeZones` VALUES (297, '+2128-07108', 'America/Grand_Turk');
INSERT INTO `sysTimeZones` VALUES (298, '+1207+01503', 'Africa/Ndjamena');
INSERT INTO `sysTimeZones` VALUES (299, '-492110+0701303', 'Indian/Kerguelen');
INSERT INTO `sysTimeZones` VALUES (300, '+0608+00113', 'Africa/Lome');
INSERT INTO `sysTimeZones` VALUES (301, '+1345+10031', 'Asia/Bangkok');
INSERT INTO `sysTimeZones` VALUES (302, '+3835+06848', 'Asia/Dushanbe');
INSERT INTO `sysTimeZones` VALUES (303, '-0922-17114', 'Pacific/Fakaofo');
INSERT INTO `sysTimeZones` VALUES (304, '+3757+05823', 'Asia/Ashkhabad');
INSERT INTO `sysTimeZones` VALUES (305, '+3648+01011', 'Africa/Tunis');
INSERT INTO `sysTimeZones` VALUES (306, '-2110+17510', 'Pacific/Tongatapu');
INSERT INTO `sysTimeZones` VALUES (307, '+4101+02858', 'Europe/Istanbul');
INSERT INTO `sysTimeZones` VALUES (308, '+1039-06131', 'America/Port_of_Spain');
INSERT INTO `sysTimeZones` VALUES (309, '-0831+17913', 'Pacific/Funafuti');
INSERT INTO `sysTimeZones` VALUES (310, '+2503+12130', 'Asia/Taipei');
INSERT INTO `sysTimeZones` VALUES (311, '-0648+03917', 'Africa/Dar_es_Salaam');
INSERT INTO `sysTimeZones` VALUES (312, '+5026+03031', 'Europe/Kiev');
INSERT INTO `sysTimeZones` VALUES (313, '+4457+03406', 'Europe/Simferopol');
INSERT INTO `sysTimeZones` VALUES (314, '+0019+03225', 'Africa/Kampala');
INSERT INTO `sysTimeZones` VALUES (315, '+1700-16830', 'Pacific/Johnston');
INSERT INTO `sysTimeZones` VALUES (316, '+2813-17722', 'Pacific/Midway');
INSERT INTO `sysTimeZones` VALUES (317, '+1917+16637', 'Pacific/Wake');
INSERT INTO `sysTimeZones` VALUES (318, '+404251-0740023', 'America/New_York');
INSERT INTO `sysTimeZones` VALUES (319, '+421953-0830245', 'America/Detroit');
INSERT INTO `sysTimeZones` VALUES (320, '+381515-0854534', 'America/Louisville');
INSERT INTO `sysTimeZones` VALUES (321, '+394606-0860929', 'America/Indianapolis');
INSERT INTO `sysTimeZones` VALUES (322, '+382232-0862041', 'America/Indiana/Marengo');
INSERT INTO `sysTimeZones` VALUES (323, '+411745-0863730', 'America/Indiana/Knox');
INSERT INTO `sysTimeZones` VALUES (324, '+384452-0850402', 'America/Indiana/Vevay');
INSERT INTO `sysTimeZones` VALUES (325, '+415100-0873900', 'America/Chicago');
INSERT INTO `sysTimeZones` VALUES (326, '+450628-0873651', 'America/Menominee');
INSERT INTO `sysTimeZones` VALUES (327, '+394421-1045903', 'America/Denver');
INSERT INTO `sysTimeZones` VALUES (328, '+433649-1161209', 'America/Boise');
INSERT INTO `sysTimeZones` VALUES (329, '+364708-1084111', 'America/Shiprock');
INSERT INTO `sysTimeZones` VALUES (330, '+332654-1120424', 'America/Phoenix');
INSERT INTO `sysTimeZones` VALUES (331, '+340308-1181434', 'America/Los_Angeles');
INSERT INTO `sysTimeZones` VALUES (332, '+611305-1495401', 'America/Anchorage');
INSERT INTO `sysTimeZones` VALUES (333, '+581807-1342511', 'America/Juneau');
INSERT INTO `sysTimeZones` VALUES (334, '+593249-1394338', 'America/Yakutat');
INSERT INTO `sysTimeZones` VALUES (335, '+643004-1652423', 'America/Nome');
INSERT INTO `sysTimeZones` VALUES (336, '+515248-1763929', 'America/Adak');
INSERT INTO `sysTimeZones` VALUES (337, '+211825-1575130', 'Pacific/Honolulu');
INSERT INTO `sysTimeZones` VALUES (338, '-3453-05611', 'America/Montevideo');
INSERT INTO `sysTimeZones` VALUES (339, '+3940+06648', 'Asia/Samarkand');
INSERT INTO `sysTimeZones` VALUES (340, '+4120+06918', 'Asia/Tashkent');
INSERT INTO `sysTimeZones` VALUES (341, '+4154+01227', 'Europe/Vatican');
INSERT INTO `sysTimeZones` VALUES (342, '+1309-06114', 'America/St_Vincent');
INSERT INTO `sysTimeZones` VALUES (343, '+1030-06656', 'America/Caracas');
INSERT INTO `sysTimeZones` VALUES (344, '+1827-06437', 'America/Tortola');
INSERT INTO `sysTimeZones` VALUES (345, '+1821-06456', 'America/St_Thomas');
INSERT INTO `sysTimeZones` VALUES (346, '+1045+10640', 'Asia/Saigon');
INSERT INTO `sysTimeZones` VALUES (347, '-1740+16825', 'Pacific/Efate');
INSERT INTO `sysTimeZones` VALUES (348, '-1318-17610', 'Pacific/Wallis');
INSERT INTO `sysTimeZones` VALUES (349, '-1350-17144', 'Pacific/Apia');
INSERT INTO `sysTimeZones` VALUES (350, '+1245+04512', 'Asia/Aden');
INSERT INTO `sysTimeZones` VALUES (351, '-1247+04514', 'Indian/Mayotte');
INSERT INTO `sysTimeZones` VALUES (352, '+4450+02030', 'Europe/Belgrade');
INSERT INTO `sysTimeZones` VALUES (353, '-2615+02800', 'Africa/Johannesburg');
INSERT INTO `sysTimeZones` VALUES (354, '-1525+02817', 'Africa/Lusaka');
INSERT INTO `sysTimeZones` VALUES (355, '-1750+03103', 'Africa/Harare');


-- --------------------------------------------------------

--
-- Table structure for table `sysUserPermissions`
--

DROP TABLE IF EXISTS `sysUserPermissions`;
CREATE TABLE IF NOT EXISTS `sysUserPermissions` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `UserID` int(11) default NULL,
  `ObjectID` varchar(32) default NULL,
  `ItemID` int(11) default NULL,
  `AccessID` tinyint(1) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `idx_group` (`UserID`),
  KEY `idx_object` (`ObjectID`)
);

INSERT INTO `sysUserPermissions` VALUES(1, 1, 'administration', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(2, 1, 'budget', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(3, 1, 'calendar', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(4, 1, 'clients', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(5, 1, 'contacts', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(6, 1, 'files', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(7, 1, 'projects', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(8, 1, 'reports', -1, 2);
INSERT INTO `sysUserPermissions` VALUES(9, 1, 'springboard', -1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tblCalendar`
--

DROP TABLE IF EXISTS `tblCalendar`;
CREATE TABLE IF NOT EXISTS `tblCalendar` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(100) default NULL,
  `Description` varchar(255) default NULL,
  `Date` date default NULL,
  `StartTime` time NOT NULL default '00:00:00',
  `EndTime` time NOT NULL default '00:00:00',
  `Colour` varchar(7) default NULL,
  `Holiday` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblClients`
--

DROP TABLE IF EXISTS `tblClients`;
CREATE TABLE IF NOT EXISTS `tblClients` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(100) default NULL,
  `Manager` int(11) default '0',
  `Phone1` varchar(30) default NULL,
  `Phone2` varchar(30) default NULL,
  `Phone3` varchar(30) default NULL,
  `FAX` varchar(30) default NULL,
  `Address1` varchar(255) default NULL,
  `Address2` varchar(30) default NULL,
  `City` varchar(30) default NULL,
  `State` varchar(30) default NULL,
  `Country` varchar(50) default NULL,
  `Postcode` varchar(11) default NULL,
  `URL` varchar(255) default NULL,
  `Description` text,
  `Archived` tinyint(1) default '0',
  `ContactName` varchar(255) default NULL,
  `ContactEmail` varchar(255) default NULL,
  `Colour` varchar(7) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `idx_archived` (`Archived`)
);

INSERT INTO `tblClients` VALUES (1, 'Default Client', 1, '', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `tblContacts`
--

DROP TABLE IF EXISTS `tblContacts`;
CREATE TABLE IF NOT EXISTS `tblContacts` (
  `ID` int(11) NOT NULL auto_increment,
  `ClientID` int(11) default NULL,
  `KeyContact` tinyint(1) NOT NULL default '0',
  `FirstName` varchar(128) default NULL,
  `LastName` varchar(128) default NULL,
  `Notes` text,
  `Title` varchar(32) default NULL,
  `EmailAddress1` varchar(255) default NULL,
  `EmailAddress2` varchar(255) default NULL,
  `Phone1` varchar(32) default NULL,
  `Phone2` varchar(32) default NULL,
  `Phone3` varchar(32) default NULL,
  `OrderBy` varchar(30) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `idx_client` (`ClientID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblEvents_Users`
--

DROP TABLE IF EXISTS `tblEvents_Users`;
CREATE TABLE IF NOT EXISTS `tblEvents_Users` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `EventID` int(10) unsigned NOT NULL default '0',
  `UserID` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblFile_Log`
--

DROP TABLE IF EXISTS `tblFile_Log`;
CREATE TABLE IF NOT EXISTS `tblFile_Log` (
  `ID` int(11) NOT NULL auto_increment,
  `FileID` int(11) NOT NULL default '0',
  `UserID` int(11) NOT NULL default '0',
  `Time` datetime NOT NULL default '0000-00-00 00:00:00',
  `Activity` varchar(11) NOT NULL default '',
  `Version` float(3,1) NOT NULL default '1.0',
  `FileName` VARCHAR( 255 ) NOT NULL,
  `Type` VARCHAR( 100 ) NOT NULL,
  `Size` INT( 11 ) NOT NULL DEFAULT 0,
  `RealName` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblFiles`
--

DROP TABLE IF EXISTS `tblFiles`;
CREATE TABLE IF NOT EXISTS `tblFiles` (
  `ID` int(11) NOT NULL auto_increment,
  `ProjectID` int(11) NOT NULL default '0',
  `TaskID` int(11) default NULL,
  `FileName` varchar(255) NOT NULL default '',
  `Description` varchar(255) default NULL,
  `Type` varchar(100) default NULL,
  `Owner` int(11) default '0',
  `Date` datetime default NULL,
  `Size` int(11) default '0',
  `Version` float(3,1) NOT NULL default '1.0',
  `RealName` text,
  `CheckedOut` tinyint(1) default '0',
  `CheckedOutUserID` int(11) default NULL,
  `Folder` int(10) unsigned NOT NULL default '0',
  `Linked` tinyint(1) default '0',
  PRIMARY KEY  (`ID`),
  KEY `idx_file_project` (`ProjectID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblFolders`
--

DROP TABLE IF EXISTS `tblFolders`;
CREATE TABLE IF NOT EXISTS `tblFolders` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ProjectID` int(10) unsigned NOT NULL default '0',
  `Folder` tinytext NOT NULL,
  `ParentID` INT NOT NULL DEFAULT 0,
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblGroups`
--

DROP TABLE IF EXISTS `tblGroups`;
CREATE TABLE IF NOT EXISTS `tblGroups` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblInvoices`
--

DROP TABLE IF EXISTS `tblInvoices`;
CREATE TABLE IF NOT EXISTS `tblInvoices` (
  `ID` int(11) NOT NULL auto_increment,
  `Quote` tinyint(3) unsigned NOT NULL default '0',
  `ProjectID` int(11) NOT NULL default '0',
  `Title` varchar(255) NOT NULL default '',
  `DateCreated` datetime NOT NULL default '0000-00-00 00:00:00',
  `CreatedBy` int(10) unsigned NOT NULL default '0',
  `Status` int(11) NOT NULL default '0',
  `Amount` varchar(255) NOT NULL default '',
  `Due` date NOT NULL default '0000-00-00',
  `EmailedTo` varchar(255) default NULL,
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblInvoices_Items`
--

DROP TABLE IF EXISTS `tblInvoices_Items`;
CREATE TABLE IF NOT EXISTS `tblInvoices_Items` (
  `ID` int(11) NOT NULL auto_increment,
  `InvoiceID` int(11) NOT NULL default '0',
  `TaskID` int(11) default NULL,
  `TaskName` varchar(255) NOT NULL default '',
  `TaskDescription` text NOT NULL default '',
  `Amount` decimal(10,2) NOT NULL default '0.00',
  `AdditionalID` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblLanguageOverride`
--

DROP TABLE IF EXISTS `tblLanguageOverride`;
CREATE TABLE IF NOT EXISTS `tblLanguageOverride` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Token` varchar(128) NOT NULL default '',
  `LangCode` varchar(5) NOT NULL default '',
  `Value` text NOT NULL,
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblProjects`
--

DROP TABLE IF EXISTS `tblProjects`;
CREATE TABLE IF NOT EXISTS `tblProjects` (
  `ID` int(11) NOT NULL auto_increment,
  `ClientID` int(11) NOT NULL default '0',
  `ProjectID` varchar(255) default NULL,
  `Name` varchar(255) default NULL,
  `Owner` int(11) default '0',
  `URL` varchar(255) default NULL,
  `DemoURL` varchar(255) default NULL,
  `StartDate` date default NULL,
  `EndDate` date default NULL,
  `ActualEndDate` date default NULL,
  `Status` int(11) default '0',
  `Priority` tinyint(1) NOT NULL default '1',
  `Colour` varchar(7) default '#EEEEEE',
  `Description` text,
  `TargetBudget` int(11) default '0',
  `ActualBudget` int(11) default '0',
  `Active` tinyint(1) default '1',
  PRIMARY KEY  (`ID`),
  KEY `idx_sdate` (`StartDate`),
  KEY `idx_edate` (`EndDate`),
  KEY `idx_ClientID` (`ClientID`),
  KEY `idx_Active` (`Active`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblTasks`
--

DROP TABLE IF EXISTS `tblTasks`;
CREATE TABLE IF NOT EXISTS `tblTasks` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(255) default NULL,
  `ProjectID` int(11) NOT NULL default '0',
  `Owner` int(11) NOT NULL default '0',
  `StartDate` date default NULL,
  `Duration` decimal(10,2) unsigned default '0.00',
  `HoursWorked` decimal(10,2) unsigned default '0.00',
  `EndDate` date default NULL,
  `Status` int(11) default '0',
  `Priority` tinyint(4) default '1',
  `PercentComplete` tinyint(4) default '0',
  `Description` text,
  `RelatedURL` varchar(255) default NULL,
  `Sequence` int(11) default NULL,
  `Indent` int(11) NOT NULL default '0',
  `TargetBudget` int(10) unsigned NOT NULL default '0',
  `ActualBudget` int(10) unsigned NOT NULL default '0',
  `LatestActivity` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  KEY `idx_task_project` (`ProjectID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblTasks_Comments`
--

DROP TABLE IF EXISTS `tblTasks_Comments`;
CREATE TABLE IF NOT EXISTS `tblTasks_Comments` (
  `ID` int(11) NOT NULL auto_increment,
  `UserID` int(11) NOT NULL default '0',
  `TaskID` int(11) NOT NULL default '0',
  `Subject` varchar(255) NOT NULL default '',
  `Body` text,
  `Date` datetime default NULL,
  `HoursWorked` decimal(4,2) default NULL,
  `CostRate` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `ChargeRate` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `Issue` tinyint(1) NOT NULL default '0',
  `Contact` int(10) unsigned NOT NULL default '0',
  `OutOfScope` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `idx_tc2` (`UserID`),
  KEY `idx_taskid` (`TaskID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblTasks_Delegation`
--

DROP TABLE IF EXISTS `tblTasks_Delegation`;
CREATE TABLE IF NOT EXISTS `tblTasks_Delegation` (
  `UserID` int(11) NOT NULL default '0',
  `TaskID` int(11) NOT NULL default '0',
  `Notified` tinyint(1) NOT NULL default '0',
  KEY `idx_UserID` (`UserID`),
  KEY `idx_Task` (`TaskID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblTasks_Dependencies`
--

DROP TABLE IF EXISTS `tblTasks_Dependencies`;
CREATE TABLE IF NOT EXISTS `tblTasks_Dependencies` (
  `ID` int(11) NOT NULL auto_increment,
  `TaskID` int(11) NOT NULL default '0',
  `TaskDependencyID` int(11) NOT NULL default '0',
  `DependencyType` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `tblUsers`
--

DROP TABLE IF EXISTS `tblUsers`;
CREATE TABLE IF NOT EXISTS `tblUsers` (
  `ID` int(11) NOT NULL auto_increment,
  `Username` varchar(32) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `Title` varchar(32) NOT NULL default '',
  `FirstName` varchar(64) default NULL,
  `LastName` varchar(64) default NULL,
  `EmailAddress` varchar(255) default NULL,
  `Phone1` varchar(30) default NULL,
  `Phone2` varchar(30) default NULL,
  `Phone3` varchar(30) default NULL,
  `Address1` varchar(64) default NULL,
  `Address2` varchar(64) default NULL,
  `City` varchar(32) default NULL,
  `State` varchar(32) default NULL,
  `Postcode` varchar(11) default NULL,
  `Country` varchar(64) default NULL,
  `Module` varchar(255) default 'projects',
  `CostRate` decimal(10,2) NOT NULL default '0.00',
  `ChargeRate` decimal(10,2) NOT NULL default '0.00',
  `Active` tinyint(1) default NULL,
  `EmailNotify` tinyint(1) NOT NULL default '1',
  `IMType` varchar(20) NOT NULL,
  `IMAccount` varchar(100) NOT NULL,
  `avatar` varchar(100) DEFAULT NULL,
  PRIMARY KEY  (`ID`),
  KEY `idx_uid` (`Username`),
  KEY `idx_pwd` (`Password`)
);

INSERT INTO `tblUsers` VALUES (1, 'admin','454C5F587EDCE2C997237182EBFC3F33', '','Administration','User','','','','','','','','','','','projects','0.00','0.00', 1, 1, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `tblUsers_Groups`
--

DROP TABLE IF EXISTS `tblUsers_Groups`;
CREATE TABLE IF NOT EXISTS `tblUsers_Groups` (
  `ID` int(11) NOT NULL auto_increment,
  `UserID` int(11) NOT NULL default '0',
  `GroupID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `UserID` (`UserID`)
);


DROP TABLE IF EXISTS `tblResource`;
CREATE TABLE IF NOT EXISTS `tblResource` (
  ID int(11) NOT NULL auto_increment,
  UserID int(11) default NULL,
  Name varchar(255) default NULL,
  AvailabilityType int(11) NOT NULL default '0',
  WeekDays varchar(20) NOT NULL,
  PRIMARY KEY (ID),
  KEY idx_UserID (UserID)
);

INSERT INTO tblResource VALUES(1,1,'',0,'');

DROP TABLE IF EXISTS `tblTaskResourceDay`;
CREATE TABLE IF NOT EXISTS `tblTaskResourceDay` (
  TaskID int(11) NOT NULL default '0',
  ResourceID int(11) NOT NULL default '0',
  DayID int(11) NOT NULL default '0',
  HoursCommitted decimal(4,2) unsigned default '0.00',
  HoursCompleted decimal(4,2) unsigned default '0.00',
  KEY idx_TaskID (TaskID),
  KEY idx_ResourceID (ResourceID),
  KEY idx_DayID (DayID)
);

DROP TABLE IF EXISTS `tblResourceDay`;
CREATE TABLE IF NOT EXISTS `tblResourceDay` (
  ResourceID int(11) NOT NULL default '0',
  DayID int(11) NOT NULL default '0',
  HoursAvailable decimal(4,2) unsigned default '0.00',
  HoursCommittedCache decimal(4,2) unsigned default '0.00',
  KEY idx_ResourceID (ResourceID),
  KEY idx_DayID (DayID)
);

DROP TABLE IF EXISTS `tblRelatedProjects`;
CREATE TABLE IF NOT EXISTS `tblRelatedProjects` (
  ProjectID int(11) NOT NULL default '0',
  RelatedProjectID int(11) NOT NULL default '0',
  KEY idx_ProjectID (ProjectID),
  KEY idx_RelatedProjectID (RelatedProjectID)
);

DROP TABLE IF EXISTS `tblTaskResource`;
CREATE TABLE IF NOT EXISTS `tblTaskResource` (
  TaskID int(11) NOT NULL default '0',
  ResourceID int(11) NOT NULL default '0',
    KEY idx_TaskID (TaskID),
    KEY idx_ResourceID (ResourceID)
);

DROP TABLE IF EXISTS `tblWorkReports`;
CREATE TABLE IF NOT EXISTS `tblWorkReports` (
  ID int(11) NOT NULL auto_increment,
  UserID int(11) NOT NULL,
  Name varchar(255) NOT NULL,
  StartDate date NOT NULL,
  EndDate date NOT NULL,
  Users varchar(255) NOT NULL,
  Clients varchar(255) NOT NULL,
  Projects varchar(255) NOT NULL,
  Frequency enum('N','W','F','M') NOT NULL,
  Created date NOT NULL,
  Period VARCHAR(20) NOT NULL,
  PRIMARY KEY  (ID)
);

DROP TABLE IF EXISTS `tblProjectReports`;
CREATE TABLE IF NOT EXISTS `tblProjectReports` (
  ID int(11) NOT NULL auto_increment,
  UserID int(11) NOT NULL,
  Name varchar(255) NOT NULL,
  StartDate date NOT NULL,
  EndDate date NOT NULL,
  Clients varchar(255) NOT NULL,
  Projects varchar(255) NOT NULL,
  Budget tinyint(4) NOT NULL,
  Details tinyint(4) NOT NULL,
  Frequency enum('N','W','F','M') NOT NULL,
  Created date NOT NULL,
  Period VARCHAR(20) NOT NULL,
  PRIMARY KEY  (ID)
);

DROP TABLE IF EXISTS `tblActivityLog`;
CREATE TABLE IF NOT EXISTS `tblActivityLog` (
  `ID` int(11) NOT NULL auto_increment,
  `Timestamp` datetime NOT NULL,
  `UserID` int(11) NOT NULL,
  `IP` varchar(20) NOT NULL,
  `Url` varchar(255) NOT NULL,
  `Context` varchar(50) NOT NULL,
  `ContextID` int(11) NOT NULL,
  `Action` varchar(50) NOT NULL,
  `Detail` varchar(255) default NULL,
  `Comment` varchar(255) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tblTimerLog`;
CREATE TABLE IF NOT EXISTS `tblTimerLog` (
  `ID` int(11) NOT NULL auto_increment,
  `Updated` datetime NOT NULL,
  `UserID` int(11) NOT NULL,
  `TaskID` int(11) NOT NULL,
  `Elapsed` time NOT NULL,
  `Paused` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tblInvoices_Items_Other`;
CREATE TABLE IF NOT EXISTS `tblInvoices_Items_Other` (
  `ID` int(11) NOT NULL auto_increment,
  `ProjectID` tinyint(4) default NULL,
  `TaskName` varchar(255) default NULL,
  `Amount` decimal(10,0) default NULL,
  `Budget` decimal(10,0) default NULL,
  `Quantity` float default NULL,
  `Cost` decimal(10,0) default NULL,
  `Charge` decimal(10,0) default NULL,
  `Logged` decimal(10,0) default NULL,
  PRIMARY KEY  (`ID`)
);

DROP VIEW IF EXISTS `vwTaskComments`;
CREATE VIEW `vwTaskComments` AS
  SELECT u.ID as UserID, CONCAT(u.FirstName, ' ', u.LastName) AS UserName,
    c.ID AS ClientID, c.Name AS ClientName, p.ID AS ProjectID,
    p.Name AS ProjectName, t.ID AS TaskID, t.Name AS TaskName,
    tc.Date AS Date, tc.HoursWorked AS HoursWorked, tc.CostRate AS CostRate,
    tc.ChargeRate AS ChargeRate,
    (tc.HoursWorked * tc.CostRate) AS Cost,
    (tc.HoursWorked * tc.ChargeRate) AS Charge,
    tc.Issue AS Issue, tc.OutOfScope AS OutOfScope
  FROM tblTasks_Comments tc
  INNER JOIN tblUsers AS u ON u.ID = tc.UserID
  INNER JOIN tblTasks AS t ON t.ID = tc.TaskID
  INNER JOIN tblProjects AS p ON p.ID = t.ProjectID
  INNER JOIN tblClients AS c ON c.ID = p.ClientID;

DROP TABLE IF EXISTS `tblDay`;
CREATE TABLE IF NOT EXISTS `tblDay` (
  ID int(11) NOT NULL auto_increment,
  Epoch int(11) NOT NULL default '0',
  Day int(11) NOT NULL default '0',
  Month int(11) NOT NULL default '0',
  Year int(11) NOT NULL default '0',
  Weekday int(11) NOT NULL default '0',
  PRIMARY KEY (ID),
  KEY idx_Epoch (Epoch),
  KEY idx_Day (Day),
  KEY idx_Month (Month),
  KEY idx_Year (Year),
  KEY idx_Weekday (Weekday)
);

--
-- days from 2006 until 2012
--
INSERT INTO tblDay VALUES (1,1136073600,1,1,2006,7);
INSERT INTO tblDay VALUES (2,1136160000,2,1,2006,1);
INSERT INTO tblDay VALUES (3,1136246400,3,1,2006,2);
INSERT INTO tblDay VALUES (4,1136332800,4,1,2006,3);
INSERT INTO tblDay VALUES (5,1136419200,5,1,2006,4);
INSERT INTO tblDay VALUES (6,1136505600,6,1,2006,5);
INSERT INTO tblDay VALUES (7,1136592000,7,1,2006,6);
INSERT INTO tblDay VALUES (8,1136678400,8,1,2006,7);
INSERT INTO tblDay VALUES (9,1136764800,9,1,2006,1);
INSERT INTO tblDay VALUES (10,1136851200,10,1,2006,2);
INSERT INTO tblDay VALUES (11,1136937600,11,1,2006,3);
INSERT INTO tblDay VALUES (12,1137024000,12,1,2006,4);
INSERT INTO tblDay VALUES (13,1137110400,13,1,2006,5);
INSERT INTO tblDay VALUES (14,1137196800,14,1,2006,6);
INSERT INTO tblDay VALUES (15,1137283200,15,1,2006,7);
INSERT INTO tblDay VALUES (16,1137369600,16,1,2006,1);
INSERT INTO tblDay VALUES (17,1137456000,17,1,2006,2);
INSERT INTO tblDay VALUES (18,1137542400,18,1,2006,3);
INSERT INTO tblDay VALUES (19,1137628800,19,1,2006,4);
INSERT INTO tblDay VALUES (20,1137715200,20,1,2006,5);
INSERT INTO tblDay VALUES (21,1137801600,21,1,2006,6);
INSERT INTO tblDay VALUES (22,1137888000,22,1,2006,7);
INSERT INTO tblDay VALUES (23,1137974400,23,1,2006,1);
INSERT INTO tblDay VALUES (24,1138060800,24,1,2006,2);
INSERT INTO tblDay VALUES (25,1138147200,25,1,2006,3);
INSERT INTO tblDay VALUES (26,1138233600,26,1,2006,4);
INSERT INTO tblDay VALUES (27,1138320000,27,1,2006,5);
INSERT INTO tblDay VALUES (28,1138406400,28,1,2006,6);
INSERT INTO tblDay VALUES (29,1138492800,29,1,2006,7);
INSERT INTO tblDay VALUES (30,1138579200,30,1,2006,1);
INSERT INTO tblDay VALUES (31,1138665600,31,1,2006,2);
INSERT INTO tblDay VALUES (32,1138752000,1,2,2006,3);
INSERT INTO tblDay VALUES (33,1138838400,2,2,2006,4);
INSERT INTO tblDay VALUES (34,1138924800,3,2,2006,5);
INSERT INTO tblDay VALUES (35,1139011200,4,2,2006,6);
INSERT INTO tblDay VALUES (36,1139097600,5,2,2006,7);
INSERT INTO tblDay VALUES (37,1139184000,6,2,2006,1);
INSERT INTO tblDay VALUES (38,1139270400,7,2,2006,2);
INSERT INTO tblDay VALUES (39,1139356800,8,2,2006,3);
INSERT INTO tblDay VALUES (40,1139443200,9,2,2006,4);
INSERT INTO tblDay VALUES (41,1139529600,10,2,2006,5);
INSERT INTO tblDay VALUES (42,1139616000,11,2,2006,6);
INSERT INTO tblDay VALUES (43,1139702400,12,2,2006,7);
INSERT INTO tblDay VALUES (44,1139788800,13,2,2006,1);
INSERT INTO tblDay VALUES (45,1139875200,14,2,2006,2);
INSERT INTO tblDay VALUES (46,1139961600,15,2,2006,3);
INSERT INTO tblDay VALUES (47,1140048000,16,2,2006,4);
INSERT INTO tblDay VALUES (48,1140134400,17,2,2006,5);
INSERT INTO tblDay VALUES (49,1140220800,18,2,2006,6);
INSERT INTO tblDay VALUES (50,1140307200,19,2,2006,7);
INSERT INTO tblDay VALUES (51,1140393600,20,2,2006,1);
INSERT INTO tblDay VALUES (52,1140480000,21,2,2006,2);
INSERT INTO tblDay VALUES (53,1140566400,22,2,2006,3);
INSERT INTO tblDay VALUES (54,1140652800,23,2,2006,4);
INSERT INTO tblDay VALUES (55,1140739200,24,2,2006,5);
INSERT INTO tblDay VALUES (56,1140825600,25,2,2006,6);
INSERT INTO tblDay VALUES (57,1140912000,26,2,2006,7);
INSERT INTO tblDay VALUES (58,1140998400,27,2,2006,1);
INSERT INTO tblDay VALUES (59,1141084800,28,2,2006,2);
INSERT INTO tblDay VALUES (60,1141171200,1,3,2006,3);
INSERT INTO tblDay VALUES (61,1141257600,2,3,2006,4);
INSERT INTO tblDay VALUES (62,1141344000,3,3,2006,5);
INSERT INTO tblDay VALUES (63,1141430400,4,3,2006,6);
INSERT INTO tblDay VALUES (64,1141516800,5,3,2006,7);
INSERT INTO tblDay VALUES (65,1141603200,6,3,2006,1);
INSERT INTO tblDay VALUES (66,1141689600,7,3,2006,2);
INSERT INTO tblDay VALUES (67,1141776000,8,3,2006,3);
INSERT INTO tblDay VALUES (68,1141862400,9,3,2006,4);
INSERT INTO tblDay VALUES (69,1141948800,10,3,2006,5);
INSERT INTO tblDay VALUES (70,1142035200,11,3,2006,6);
INSERT INTO tblDay VALUES (71,1142121600,12,3,2006,7);
INSERT INTO tblDay VALUES (72,1142208000,13,3,2006,1);
INSERT INTO tblDay VALUES (73,1142294400,14,3,2006,2);
INSERT INTO tblDay VALUES (74,1142380800,15,3,2006,3);
INSERT INTO tblDay VALUES (75,1142467200,16,3,2006,4);
INSERT INTO tblDay VALUES (76,1142553600,17,3,2006,5);
INSERT INTO tblDay VALUES (77,1142640000,18,3,2006,6);
INSERT INTO tblDay VALUES (78,1142726400,19,3,2006,7);
INSERT INTO tblDay VALUES (79,1142812800,20,3,2006,1);
INSERT INTO tblDay VALUES (80,1142899200,21,3,2006,2);
INSERT INTO tblDay VALUES (81,1142985600,22,3,2006,3);
INSERT INTO tblDay VALUES (82,1143072000,23,3,2006,4);
INSERT INTO tblDay VALUES (83,1143158400,24,3,2006,5);
INSERT INTO tblDay VALUES (84,1143244800,25,3,2006,6);
INSERT INTO tblDay VALUES (85,1143331200,26,3,2006,7);
INSERT INTO tblDay VALUES (86,1143417600,27,3,2006,1);
INSERT INTO tblDay VALUES (87,1143504000,28,3,2006,2);
INSERT INTO tblDay VALUES (88,1143590400,29,3,2006,3);
INSERT INTO tblDay VALUES (89,1143676800,30,3,2006,4);
INSERT INTO tblDay VALUES (90,1143763200,31,3,2006,5);
INSERT INTO tblDay VALUES (91,1143849600,1,4,2006,6);
INSERT INTO tblDay VALUES (92,1143936000,2,4,2006,7);
INSERT INTO tblDay VALUES (93,1144022400,3,4,2006,1);
INSERT INTO tblDay VALUES (94,1144108800,4,4,2006,2);
INSERT INTO tblDay VALUES (95,1144195200,5,4,2006,3);
INSERT INTO tblDay VALUES (96,1144281600,6,4,2006,4);
INSERT INTO tblDay VALUES (97,1144368000,7,4,2006,5);
INSERT INTO tblDay VALUES (98,1144454400,8,4,2006,6);
INSERT INTO tblDay VALUES (99,1144540800,9,4,2006,7);
INSERT INTO tblDay VALUES (100,1144627200,10,4,2006,1);
INSERT INTO tblDay VALUES (101,1144713600,11,4,2006,2);
INSERT INTO tblDay VALUES (102,1144800000,12,4,2006,3);
INSERT INTO tblDay VALUES (103,1144886400,13,4,2006,4);
INSERT INTO tblDay VALUES (104,1144972800,14,4,2006,5);
INSERT INTO tblDay VALUES (105,1145059200,15,4,2006,6);
INSERT INTO tblDay VALUES (106,1145145600,16,4,2006,7);
INSERT INTO tblDay VALUES (107,1145232000,17,4,2006,1);
INSERT INTO tblDay VALUES (108,1145318400,18,4,2006,2);
INSERT INTO tblDay VALUES (109,1145404800,19,4,2006,3);
INSERT INTO tblDay VALUES (110,1145491200,20,4,2006,4);
INSERT INTO tblDay VALUES (111,1145577600,21,4,2006,5);
INSERT INTO tblDay VALUES (112,1145664000,22,4,2006,6);
INSERT INTO tblDay VALUES (113,1145750400,23,4,2006,7);
INSERT INTO tblDay VALUES (114,1145836800,24,4,2006,1);
INSERT INTO tblDay VALUES (115,1145923200,25,4,2006,2);
INSERT INTO tblDay VALUES (116,1146009600,26,4,2006,3);
INSERT INTO tblDay VALUES (117,1146096000,27,4,2006,4);
INSERT INTO tblDay VALUES (118,1146182400,28,4,2006,5);
INSERT INTO tblDay VALUES (119,1146268800,29,4,2006,6);
INSERT INTO tblDay VALUES (120,1146355200,30,4,2006,7);
INSERT INTO tblDay VALUES (121,1146441600,1,5,2006,1);
INSERT INTO tblDay VALUES (122,1146528000,2,5,2006,2);
INSERT INTO tblDay VALUES (123,1146614400,3,5,2006,3);
INSERT INTO tblDay VALUES (124,1146700800,4,5,2006,4);
INSERT INTO tblDay VALUES (125,1146787200,5,5,2006,5);
INSERT INTO tblDay VALUES (126,1146873600,6,5,2006,6);
INSERT INTO tblDay VALUES (127,1146960000,7,5,2006,7);
INSERT INTO tblDay VALUES (128,1147046400,8,5,2006,1);
INSERT INTO tblDay VALUES (129,1147132800,9,5,2006,2);
INSERT INTO tblDay VALUES (130,1147219200,10,5,2006,3);
INSERT INTO tblDay VALUES (131,1147305600,11,5,2006,4);
INSERT INTO tblDay VALUES (132,1147392000,12,5,2006,5);
INSERT INTO tblDay VALUES (133,1147478400,13,5,2006,6);
INSERT INTO tblDay VALUES (134,1147564800,14,5,2006,7);
INSERT INTO tblDay VALUES (135,1147651200,15,5,2006,1);
INSERT INTO tblDay VALUES (136,1147737600,16,5,2006,2);
INSERT INTO tblDay VALUES (137,1147824000,17,5,2006,3);
INSERT INTO tblDay VALUES (138,1147910400,18,5,2006,4);
INSERT INTO tblDay VALUES (139,1147996800,19,5,2006,5);
INSERT INTO tblDay VALUES (140,1148083200,20,5,2006,6);
INSERT INTO tblDay VALUES (141,1148169600,21,5,2006,7);
INSERT INTO tblDay VALUES (142,1148256000,22,5,2006,1);
INSERT INTO tblDay VALUES (143,1148342400,23,5,2006,2);
INSERT INTO tblDay VALUES (144,1148428800,24,5,2006,3);
INSERT INTO tblDay VALUES (145,1148515200,25,5,2006,4);
INSERT INTO tblDay VALUES (146,1148601600,26,5,2006,5);
INSERT INTO tblDay VALUES (147,1148688000,27,5,2006,6);
INSERT INTO tblDay VALUES (148,1148774400,28,5,2006,7);
INSERT INTO tblDay VALUES (149,1148860800,29,5,2006,1);
INSERT INTO tblDay VALUES (150,1148947200,30,5,2006,2);
INSERT INTO tblDay VALUES (151,1149033600,31,5,2006,3);
INSERT INTO tblDay VALUES (152,1149120000,1,6,2006,4);
INSERT INTO tblDay VALUES (153,1149206400,2,6,2006,5);
INSERT INTO tblDay VALUES (154,1149292800,3,6,2006,6);
INSERT INTO tblDay VALUES (155,1149379200,4,6,2006,7);
INSERT INTO tblDay VALUES (156,1149465600,5,6,2006,1);
INSERT INTO tblDay VALUES (157,1149552000,6,6,2006,2);
INSERT INTO tblDay VALUES (158,1149638400,7,6,2006,3);
INSERT INTO tblDay VALUES (159,1149724800,8,6,2006,4);
INSERT INTO tblDay VALUES (160,1149811200,9,6,2006,5);
INSERT INTO tblDay VALUES (161,1149897600,10,6,2006,6);
INSERT INTO tblDay VALUES (162,1149984000,11,6,2006,7);
INSERT INTO tblDay VALUES (163,1150070400,12,6,2006,1);
INSERT INTO tblDay VALUES (164,1150156800,13,6,2006,2);
INSERT INTO tblDay VALUES (165,1150243200,14,6,2006,3);
INSERT INTO tblDay VALUES (166,1150329600,15,6,2006,4);
INSERT INTO tblDay VALUES (167,1150416000,16,6,2006,5);
INSERT INTO tblDay VALUES (168,1150502400,17,6,2006,6);
INSERT INTO tblDay VALUES (169,1150588800,18,6,2006,7);
INSERT INTO tblDay VALUES (170,1150675200,19,6,2006,1);
INSERT INTO tblDay VALUES (171,1150761600,20,6,2006,2);
INSERT INTO tblDay VALUES (172,1150848000,21,6,2006,3);
INSERT INTO tblDay VALUES (173,1150934400,22,6,2006,4);
INSERT INTO tblDay VALUES (174,1151020800,23,6,2006,5);
INSERT INTO tblDay VALUES (175,1151107200,24,6,2006,6);
INSERT INTO tblDay VALUES (176,1151193600,25,6,2006,7);
INSERT INTO tblDay VALUES (177,1151280000,26,6,2006,1);
INSERT INTO tblDay VALUES (178,1151366400,27,6,2006,2);
INSERT INTO tblDay VALUES (179,1151452800,28,6,2006,3);
INSERT INTO tblDay VALUES (180,1151539200,29,6,2006,4);
INSERT INTO tblDay VALUES (181,1151625600,30,6,2006,5);
INSERT INTO tblDay VALUES (182,1151712000,1,7,2006,6);
INSERT INTO tblDay VALUES (183,1151798400,2,7,2006,7);
INSERT INTO tblDay VALUES (184,1151884800,3,7,2006,1);
INSERT INTO tblDay VALUES (185,1151971200,4,7,2006,2);
INSERT INTO tblDay VALUES (186,1152057600,5,7,2006,3);
INSERT INTO tblDay VALUES (187,1152144000,6,7,2006,4);
INSERT INTO tblDay VALUES (188,1152230400,7,7,2006,5);
INSERT INTO tblDay VALUES (189,1152316800,8,7,2006,6);
INSERT INTO tblDay VALUES (190,1152403200,9,7,2006,7);
INSERT INTO tblDay VALUES (191,1152489600,10,7,2006,1);
INSERT INTO tblDay VALUES (192,1152576000,11,7,2006,2);
INSERT INTO tblDay VALUES (193,1152662400,12,7,2006,3);
INSERT INTO tblDay VALUES (194,1152748800,13,7,2006,4);
INSERT INTO tblDay VALUES (195,1152835200,14,7,2006,5);
INSERT INTO tblDay VALUES (196,1152921600,15,7,2006,6);
INSERT INTO tblDay VALUES (197,1153008000,16,7,2006,7);
INSERT INTO tblDay VALUES (198,1153094400,17,7,2006,1);
INSERT INTO tblDay VALUES (199,1153180800,18,7,2006,2);
INSERT INTO tblDay VALUES (200,1153267200,19,7,2006,3);
INSERT INTO tblDay VALUES (201,1153353600,20,7,2006,4);
INSERT INTO tblDay VALUES (202,1153440000,21,7,2006,5);
INSERT INTO tblDay VALUES (203,1153526400,22,7,2006,6);
INSERT INTO tblDay VALUES (204,1153612800,23,7,2006,7);
INSERT INTO tblDay VALUES (205,1153699200,24,7,2006,1);
INSERT INTO tblDay VALUES (206,1153785600,25,7,2006,2);
INSERT INTO tblDay VALUES (207,1153872000,26,7,2006,3);
INSERT INTO tblDay VALUES (208,1153958400,27,7,2006,4);
INSERT INTO tblDay VALUES (209,1154044800,28,7,2006,5);
INSERT INTO tblDay VALUES (210,1154131200,29,7,2006,6);
INSERT INTO tblDay VALUES (211,1154217600,30,7,2006,7);
INSERT INTO tblDay VALUES (212,1154304000,31,7,2006,1);
INSERT INTO tblDay VALUES (213,1154390400,1,8,2006,2);
INSERT INTO tblDay VALUES (214,1154476800,2,8,2006,3);
INSERT INTO tblDay VALUES (215,1154563200,3,8,2006,4);
INSERT INTO tblDay VALUES (216,1154649600,4,8,2006,5);
INSERT INTO tblDay VALUES (217,1154736000,5,8,2006,6);
INSERT INTO tblDay VALUES (218,1154822400,6,8,2006,7);
INSERT INTO tblDay VALUES (219,1154908800,7,8,2006,1);
INSERT INTO tblDay VALUES (220,1154995200,8,8,2006,2);
INSERT INTO tblDay VALUES (221,1155081600,9,8,2006,3);
INSERT INTO tblDay VALUES (222,1155168000,10,8,2006,4);
INSERT INTO tblDay VALUES (223,1155254400,11,8,2006,5);
INSERT INTO tblDay VALUES (224,1155340800,12,8,2006,6);
INSERT INTO tblDay VALUES (225,1155427200,13,8,2006,7);
INSERT INTO tblDay VALUES (226,1155513600,14,8,2006,1);
INSERT INTO tblDay VALUES (227,1155600000,15,8,2006,2);
INSERT INTO tblDay VALUES (228,1155686400,16,8,2006,3);
INSERT INTO tblDay VALUES (229,1155772800,17,8,2006,4);
INSERT INTO tblDay VALUES (230,1155859200,18,8,2006,5);
INSERT INTO tblDay VALUES (231,1155945600,19,8,2006,6);
INSERT INTO tblDay VALUES (232,1156032000,20,8,2006,7);
INSERT INTO tblDay VALUES (233,1156118400,21,8,2006,1);
INSERT INTO tblDay VALUES (234,1156204800,22,8,2006,2);
INSERT INTO tblDay VALUES (235,1156291200,23,8,2006,3);
INSERT INTO tblDay VALUES (236,1156377600,24,8,2006,4);
INSERT INTO tblDay VALUES (237,1156464000,25,8,2006,5);
INSERT INTO tblDay VALUES (238,1156550400,26,8,2006,6);
INSERT INTO tblDay VALUES (239,1156636800,27,8,2006,7);
INSERT INTO tblDay VALUES (240,1156723200,28,8,2006,1);
INSERT INTO tblDay VALUES (241,1156809600,29,8,2006,2);
INSERT INTO tblDay VALUES (242,1156896000,30,8,2006,3);
INSERT INTO tblDay VALUES (243,1156982400,31,8,2006,4);
INSERT INTO tblDay VALUES (244,1157068800,1,9,2006,5);
INSERT INTO tblDay VALUES (245,1157155200,2,9,2006,6);
INSERT INTO tblDay VALUES (246,1157241600,3,9,2006,7);
INSERT INTO tblDay VALUES (247,1157328000,4,9,2006,1);
INSERT INTO tblDay VALUES (248,1157414400,5,9,2006,2);
INSERT INTO tblDay VALUES (249,1157500800,6,9,2006,3);
INSERT INTO tblDay VALUES (250,1157587200,7,9,2006,4);
INSERT INTO tblDay VALUES (251,1157673600,8,9,2006,5);
INSERT INTO tblDay VALUES (252,1157760000,9,9,2006,6);
INSERT INTO tblDay VALUES (253,1157846400,10,9,2006,7);
INSERT INTO tblDay VALUES (254,1157932800,11,9,2006,1);
INSERT INTO tblDay VALUES (255,1158019200,12,9,2006,2);
INSERT INTO tblDay VALUES (256,1158105600,13,9,2006,3);
INSERT INTO tblDay VALUES (257,1158192000,14,9,2006,4);
INSERT INTO tblDay VALUES (258,1158278400,15,9,2006,5);
INSERT INTO tblDay VALUES (259,1158364800,16,9,2006,6);
INSERT INTO tblDay VALUES (260,1158451200,17,9,2006,7);
INSERT INTO tblDay VALUES (261,1158537600,18,9,2006,1);
INSERT INTO tblDay VALUES (262,1158624000,19,9,2006,2);
INSERT INTO tblDay VALUES (263,1158710400,20,9,2006,3);
INSERT INTO tblDay VALUES (264,1158796800,21,9,2006,4);
INSERT INTO tblDay VALUES (265,1158883200,22,9,2006,5);
INSERT INTO tblDay VALUES (266,1158969600,23,9,2006,6);
INSERT INTO tblDay VALUES (267,1159056000,24,9,2006,7);
INSERT INTO tblDay VALUES (268,1159142400,25,9,2006,1);
INSERT INTO tblDay VALUES (269,1159228800,26,9,2006,2);
INSERT INTO tblDay VALUES (270,1159315200,27,9,2006,3);
INSERT INTO tblDay VALUES (271,1159401600,28,9,2006,4);
INSERT INTO tblDay VALUES (272,1159488000,29,9,2006,5);
INSERT INTO tblDay VALUES (273,1159574400,30,9,2006,6);
INSERT INTO tblDay VALUES (274,1159660800,1,10,2006,7);
INSERT INTO tblDay VALUES (275,1159747200,2,10,2006,1);
INSERT INTO tblDay VALUES (276,1159833600,3,10,2006,2);
INSERT INTO tblDay VALUES (277,1159920000,4,10,2006,3);
INSERT INTO tblDay VALUES (278,1160006400,5,10,2006,4);
INSERT INTO tblDay VALUES (279,1160092800,6,10,2006,5);
INSERT INTO tblDay VALUES (280,1160179200,7,10,2006,6);
INSERT INTO tblDay VALUES (281,1160265600,8,10,2006,7);
INSERT INTO tblDay VALUES (282,1160352000,9,10,2006,1);
INSERT INTO tblDay VALUES (283,1160438400,10,10,2006,2);
INSERT INTO tblDay VALUES (284,1160524800,11,10,2006,3);
INSERT INTO tblDay VALUES (285,1160611200,12,10,2006,4);
INSERT INTO tblDay VALUES (286,1160697600,13,10,2006,5);
INSERT INTO tblDay VALUES (287,1160784000,14,10,2006,6);
INSERT INTO tblDay VALUES (288,1160870400,15,10,2006,7);
INSERT INTO tblDay VALUES (289,1160956800,16,10,2006,1);
INSERT INTO tblDay VALUES (290,1161043200,17,10,2006,2);
INSERT INTO tblDay VALUES (291,1161129600,18,10,2006,3);
INSERT INTO tblDay VALUES (292,1161216000,19,10,2006,4);
INSERT INTO tblDay VALUES (293,1161302400,20,10,2006,5);
INSERT INTO tblDay VALUES (294,1161388800,21,10,2006,6);
INSERT INTO tblDay VALUES (295,1161475200,22,10,2006,7);
INSERT INTO tblDay VALUES (296,1161561600,23,10,2006,1);
INSERT INTO tblDay VALUES (297,1161648000,24,10,2006,2);
INSERT INTO tblDay VALUES (298,1161734400,25,10,2006,3);
INSERT INTO tblDay VALUES (299,1161820800,26,10,2006,4);
INSERT INTO tblDay VALUES (300,1161907200,27,10,2006,5);
INSERT INTO tblDay VALUES (301,1161993600,28,10,2006,6);
INSERT INTO tblDay VALUES (302,1162080000,29,10,2006,7);
INSERT INTO tblDay VALUES (303,1162166400,30,10,2006,1);
INSERT INTO tblDay VALUES (304,1162252800,31,10,2006,2);
INSERT INTO tblDay VALUES (305,1162339200,1,11,2006,3);
INSERT INTO tblDay VALUES (306,1162425600,2,11,2006,4);
INSERT INTO tblDay VALUES (307,1162512000,3,11,2006,5);
INSERT INTO tblDay VALUES (308,1162598400,4,11,2006,6);
INSERT INTO tblDay VALUES (309,1162684800,5,11,2006,7);
INSERT INTO tblDay VALUES (310,1162771200,6,11,2006,1);
INSERT INTO tblDay VALUES (311,1162857600,7,11,2006,2);
INSERT INTO tblDay VALUES (312,1162944000,8,11,2006,3);
INSERT INTO tblDay VALUES (313,1163030400,9,11,2006,4);
INSERT INTO tblDay VALUES (314,1163116800,10,11,2006,5);
INSERT INTO tblDay VALUES (315,1163203200,11,11,2006,6);
INSERT INTO tblDay VALUES (316,1163289600,12,11,2006,7);
INSERT INTO tblDay VALUES (317,1163376000,13,11,2006,1);
INSERT INTO tblDay VALUES (318,1163462400,14,11,2006,2);
INSERT INTO tblDay VALUES (319,1163548800,15,11,2006,3);
INSERT INTO tblDay VALUES (320,1163635200,16,11,2006,4);
INSERT INTO tblDay VALUES (321,1163721600,17,11,2006,5);
INSERT INTO tblDay VALUES (322,1163808000,18,11,2006,6);
INSERT INTO tblDay VALUES (323,1163894400,19,11,2006,7);
INSERT INTO tblDay VALUES (324,1163980800,20,11,2006,1);
INSERT INTO tblDay VALUES (325,1164067200,21,11,2006,2);
INSERT INTO tblDay VALUES (326,1164153600,22,11,2006,3);
INSERT INTO tblDay VALUES (327,1164240000,23,11,2006,4);
INSERT INTO tblDay VALUES (328,1164326400,24,11,2006,5);
INSERT INTO tblDay VALUES (329,1164412800,25,11,2006,6);
INSERT INTO tblDay VALUES (330,1164499200,26,11,2006,7);
INSERT INTO tblDay VALUES (331,1164585600,27,11,2006,1);
INSERT INTO tblDay VALUES (332,1164672000,28,11,2006,2);
INSERT INTO tblDay VALUES (333,1164758400,29,11,2006,3);
INSERT INTO tblDay VALUES (334,1164844800,30,11,2006,4);
INSERT INTO tblDay VALUES (335,1164931200,1,12,2006,5);
INSERT INTO tblDay VALUES (336,1165017600,2,12,2006,6);
INSERT INTO tblDay VALUES (337,1165104000,3,12,2006,7);
INSERT INTO tblDay VALUES (338,1165190400,4,12,2006,1);
INSERT INTO tblDay VALUES (339,1165276800,5,12,2006,2);
INSERT INTO tblDay VALUES (340,1165363200,6,12,2006,3);
INSERT INTO tblDay VALUES (341,1165449600,7,12,2006,4);
INSERT INTO tblDay VALUES (342,1165536000,8,12,2006,5);
INSERT INTO tblDay VALUES (343,1165622400,9,12,2006,6);
INSERT INTO tblDay VALUES (344,1165708800,10,12,2006,7);
INSERT INTO tblDay VALUES (345,1165795200,11,12,2006,1);
INSERT INTO tblDay VALUES (346,1165881600,12,12,2006,2);
INSERT INTO tblDay VALUES (347,1165968000,13,12,2006,3);
INSERT INTO tblDay VALUES (348,1166054400,14,12,2006,4);
INSERT INTO tblDay VALUES (349,1166140800,15,12,2006,5);
INSERT INTO tblDay VALUES (350,1166227200,16,12,2006,6);
INSERT INTO tblDay VALUES (351,1166313600,17,12,2006,7);
INSERT INTO tblDay VALUES (352,1166400000,18,12,2006,1);
INSERT INTO tblDay VALUES (353,1166486400,19,12,2006,2);
INSERT INTO tblDay VALUES (354,1166572800,20,12,2006,3);
INSERT INTO tblDay VALUES (355,1166659200,21,12,2006,4);
INSERT INTO tblDay VALUES (356,1166745600,22,12,2006,5);
INSERT INTO tblDay VALUES (357,1166832000,23,12,2006,6);
INSERT INTO tblDay VALUES (358,1166918400,24,12,2006,7);
INSERT INTO tblDay VALUES (359,1167004800,25,12,2006,1);
INSERT INTO tblDay VALUES (360,1167091200,26,12,2006,2);
INSERT INTO tblDay VALUES (361,1167177600,27,12,2006,3);
INSERT INTO tblDay VALUES (362,1167264000,28,12,2006,4);
INSERT INTO tblDay VALUES (363,1167350400,29,12,2006,5);
INSERT INTO tblDay VALUES (364,1167436800,30,12,2006,6);
INSERT INTO tblDay VALUES (365,1167523200,31,12,2006,7);
INSERT INTO tblDay VALUES (366,1167609600,1,1,2007,1);
INSERT INTO tblDay VALUES (367,1167696000,2,1,2007,2);
INSERT INTO tblDay VALUES (368,1167782400,3,1,2007,3);
INSERT INTO tblDay VALUES (369,1167868800,4,1,2007,4);
INSERT INTO tblDay VALUES (370,1167955200,5,1,2007,5);
INSERT INTO tblDay VALUES (371,1168041600,6,1,2007,6);
INSERT INTO tblDay VALUES (372,1168128000,7,1,2007,7);
INSERT INTO tblDay VALUES (373,1168214400,8,1,2007,1);
INSERT INTO tblDay VALUES (374,1168300800,9,1,2007,2);
INSERT INTO tblDay VALUES (375,1168387200,10,1,2007,3);
INSERT INTO tblDay VALUES (376,1168473600,11,1,2007,4);
INSERT INTO tblDay VALUES (377,1168560000,12,1,2007,5);
INSERT INTO tblDay VALUES (378,1168646400,13,1,2007,6);
INSERT INTO tblDay VALUES (379,1168732800,14,1,2007,7);
INSERT INTO tblDay VALUES (380,1168819200,15,1,2007,1);
INSERT INTO tblDay VALUES (381,1168905600,16,1,2007,2);
INSERT INTO tblDay VALUES (382,1168992000,17,1,2007,3);
INSERT INTO tblDay VALUES (383,1169078400,18,1,2007,4);
INSERT INTO tblDay VALUES (384,1169164800,19,1,2007,5);
INSERT INTO tblDay VALUES (385,1169251200,20,1,2007,6);
INSERT INTO tblDay VALUES (386,1169337600,21,1,2007,7);
INSERT INTO tblDay VALUES (387,1169424000,22,1,2007,1);
INSERT INTO tblDay VALUES (388,1169510400,23,1,2007,2);
INSERT INTO tblDay VALUES (389,1169596800,24,1,2007,3);
INSERT INTO tblDay VALUES (390,1169683200,25,1,2007,4);
INSERT INTO tblDay VALUES (391,1169769600,26,1,2007,5);
INSERT INTO tblDay VALUES (392,1169856000,27,1,2007,6);
INSERT INTO tblDay VALUES (393,1169942400,28,1,2007,7);
INSERT INTO tblDay VALUES (394,1170028800,29,1,2007,1);
INSERT INTO tblDay VALUES (395,1170115200,30,1,2007,2);
INSERT INTO tblDay VALUES (396,1170201600,31,1,2007,3);
INSERT INTO tblDay VALUES (397,1170288000,1,2,2007,4);
INSERT INTO tblDay VALUES (398,1170374400,2,2,2007,5);
INSERT INTO tblDay VALUES (399,1170460800,3,2,2007,6);
INSERT INTO tblDay VALUES (400,1170547200,4,2,2007,7);
INSERT INTO tblDay VALUES (401,1170633600,5,2,2007,1);
INSERT INTO tblDay VALUES (402,1170720000,6,2,2007,2);
INSERT INTO tblDay VALUES (403,1170806400,7,2,2007,3);
INSERT INTO tblDay VALUES (404,1170892800,8,2,2007,4);
INSERT INTO tblDay VALUES (405,1170979200,9,2,2007,5);
INSERT INTO tblDay VALUES (406,1171065600,10,2,2007,6);
INSERT INTO tblDay VALUES (407,1171152000,11,2,2007,7);
INSERT INTO tblDay VALUES (408,1171238400,12,2,2007,1);
INSERT INTO tblDay VALUES (409,1171324800,13,2,2007,2);
INSERT INTO tblDay VALUES (410,1171411200,14,2,2007,3);
INSERT INTO tblDay VALUES (411,1171497600,15,2,2007,4);
INSERT INTO tblDay VALUES (412,1171584000,16,2,2007,5);
INSERT INTO tblDay VALUES (413,1171670400,17,2,2007,6);
INSERT INTO tblDay VALUES (414,1171756800,18,2,2007,7);
INSERT INTO tblDay VALUES (415,1171843200,19,2,2007,1);
INSERT INTO tblDay VALUES (416,1171929600,20,2,2007,2);
INSERT INTO tblDay VALUES (417,1172016000,21,2,2007,3);
INSERT INTO tblDay VALUES (418,1172102400,22,2,2007,4);
INSERT INTO tblDay VALUES (419,1172188800,23,2,2007,5);
INSERT INTO tblDay VALUES (420,1172275200,24,2,2007,6);
INSERT INTO tblDay VALUES (421,1172361600,25,2,2007,7);
INSERT INTO tblDay VALUES (422,1172448000,26,2,2007,1);
INSERT INTO tblDay VALUES (423,1172534400,27,2,2007,2);
INSERT INTO tblDay VALUES (424,1172620800,28,2,2007,3);
INSERT INTO tblDay VALUES (425,1172707200,1,3,2007,4);
INSERT INTO tblDay VALUES (426,1172793600,2,3,2007,5);
INSERT INTO tblDay VALUES (427,1172880000,3,3,2007,6);
INSERT INTO tblDay VALUES (428,1172966400,4,3,2007,7);
INSERT INTO tblDay VALUES (429,1173052800,5,3,2007,1);
INSERT INTO tblDay VALUES (430,1173139200,6,3,2007,2);
INSERT INTO tblDay VALUES (431,1173225600,7,3,2007,3);
INSERT INTO tblDay VALUES (432,1173312000,8,3,2007,4);
INSERT INTO tblDay VALUES (433,1173398400,9,3,2007,5);
INSERT INTO tblDay VALUES (434,1173484800,10,3,2007,6);
INSERT INTO tblDay VALUES (435,1173571200,11,3,2007,7);
INSERT INTO tblDay VALUES (436,1173657600,12,3,2007,1);
INSERT INTO tblDay VALUES (437,1173744000,13,3,2007,2);
INSERT INTO tblDay VALUES (438,1173830400,14,3,2007,3);
INSERT INTO tblDay VALUES (439,1173916800,15,3,2007,4);
INSERT INTO tblDay VALUES (440,1174003200,16,3,2007,5);
INSERT INTO tblDay VALUES (441,1174089600,17,3,2007,6);
INSERT INTO tblDay VALUES (442,1174176000,18,3,2007,7);
INSERT INTO tblDay VALUES (443,1174262400,19,3,2007,1);
INSERT INTO tblDay VALUES (444,1174348800,20,3,2007,2);
INSERT INTO tblDay VALUES (445,1174435200,21,3,2007,3);
INSERT INTO tblDay VALUES (446,1174521600,22,3,2007,4);
INSERT INTO tblDay VALUES (447,1174608000,23,3,2007,5);
INSERT INTO tblDay VALUES (448,1174694400,24,3,2007,6);
INSERT INTO tblDay VALUES (449,1174780800,25,3,2007,7);
INSERT INTO tblDay VALUES (450,1174867200,26,3,2007,1);
INSERT INTO tblDay VALUES (451,1174953600,27,3,2007,2);
INSERT INTO tblDay VALUES (452,1175040000,28,3,2007,3);
INSERT INTO tblDay VALUES (453,1175126400,29,3,2007,4);
INSERT INTO tblDay VALUES (454,1175212800,30,3,2007,5);
INSERT INTO tblDay VALUES (455,1175299200,31,3,2007,6);
INSERT INTO tblDay VALUES (456,1175385600,1,4,2007,7);
INSERT INTO tblDay VALUES (457,1175472000,2,4,2007,1);
INSERT INTO tblDay VALUES (458,1175558400,3,4,2007,2);
INSERT INTO tblDay VALUES (459,1175644800,4,4,2007,3);
INSERT INTO tblDay VALUES (460,1175731200,5,4,2007,4);
INSERT INTO tblDay VALUES (461,1175817600,6,4,2007,5);
INSERT INTO tblDay VALUES (462,1175904000,7,4,2007,6);
INSERT INTO tblDay VALUES (463,1175990400,8,4,2007,7);
INSERT INTO tblDay VALUES (464,1176076800,9,4,2007,1);
INSERT INTO tblDay VALUES (465,1176163200,10,4,2007,2);
INSERT INTO tblDay VALUES (466,1176249600,11,4,2007,3);
INSERT INTO tblDay VALUES (467,1176336000,12,4,2007,4);
INSERT INTO tblDay VALUES (468,1176422400,13,4,2007,5);
INSERT INTO tblDay VALUES (469,1176508800,14,4,2007,6);
INSERT INTO tblDay VALUES (470,1176595200,15,4,2007,7);
INSERT INTO tblDay VALUES (471,1176681600,16,4,2007,1);
INSERT INTO tblDay VALUES (472,1176768000,17,4,2007,2);
INSERT INTO tblDay VALUES (473,1176854400,18,4,2007,3);
INSERT INTO tblDay VALUES (474,1176940800,19,4,2007,4);
INSERT INTO tblDay VALUES (475,1177027200,20,4,2007,5);
INSERT INTO tblDay VALUES (476,1177113600,21,4,2007,6);
INSERT INTO tblDay VALUES (477,1177200000,22,4,2007,7);
INSERT INTO tblDay VALUES (478,1177286400,23,4,2007,1);
INSERT INTO tblDay VALUES (479,1177372800,24,4,2007,2);
INSERT INTO tblDay VALUES (480,1177459200,25,4,2007,3);
INSERT INTO tblDay VALUES (481,1177545600,26,4,2007,4);
INSERT INTO tblDay VALUES (482,1177632000,27,4,2007,5);
INSERT INTO tblDay VALUES (483,1177718400,28,4,2007,6);
INSERT INTO tblDay VALUES (484,1177804800,29,4,2007,7);
INSERT INTO tblDay VALUES (485,1177891200,30,4,2007,1);
INSERT INTO tblDay VALUES (486,1177977600,1,5,2007,2);
INSERT INTO tblDay VALUES (487,1178064000,2,5,2007,3);
INSERT INTO tblDay VALUES (488,1178150400,3,5,2007,4);
INSERT INTO tblDay VALUES (489,1178236800,4,5,2007,5);
INSERT INTO tblDay VALUES (490,1178323200,5,5,2007,6);
INSERT INTO tblDay VALUES (491,1178409600,6,5,2007,7);
INSERT INTO tblDay VALUES (492,1178496000,7,5,2007,1);
INSERT INTO tblDay VALUES (493,1178582400,8,5,2007,2);
INSERT INTO tblDay VALUES (494,1178668800,9,5,2007,3);
INSERT INTO tblDay VALUES (495,1178755200,10,5,2007,4);
INSERT INTO tblDay VALUES (496,1178841600,11,5,2007,5);
INSERT INTO tblDay VALUES (497,1178928000,12,5,2007,6);
INSERT INTO tblDay VALUES (498,1179014400,13,5,2007,7);
INSERT INTO tblDay VALUES (499,1179100800,14,5,2007,1);
INSERT INTO tblDay VALUES (500,1179187200,15,5,2007,2);
INSERT INTO tblDay VALUES (501,1179273600,16,5,2007,3);
INSERT INTO tblDay VALUES (502,1179360000,17,5,2007,4);
INSERT INTO tblDay VALUES (503,1179446400,18,5,2007,5);
INSERT INTO tblDay VALUES (504,1179532800,19,5,2007,6);
INSERT INTO tblDay VALUES (505,1179619200,20,5,2007,7);
INSERT INTO tblDay VALUES (506,1179705600,21,5,2007,1);
INSERT INTO tblDay VALUES (507,1179792000,22,5,2007,2);
INSERT INTO tblDay VALUES (508,1179878400,23,5,2007,3);
INSERT INTO tblDay VALUES (509,1179964800,24,5,2007,4);
INSERT INTO tblDay VALUES (510,1180051200,25,5,2007,5);
INSERT INTO tblDay VALUES (511,1180137600,26,5,2007,6);
INSERT INTO tblDay VALUES (512,1180224000,27,5,2007,7);
INSERT INTO tblDay VALUES (513,1180310400,28,5,2007,1);
INSERT INTO tblDay VALUES (514,1180396800,29,5,2007,2);
INSERT INTO tblDay VALUES (515,1180483200,30,5,2007,3);
INSERT INTO tblDay VALUES (516,1180569600,31,5,2007,4);
INSERT INTO tblDay VALUES (517,1180656000,1,6,2007,5);
INSERT INTO tblDay VALUES (518,1180742400,2,6,2007,6);
INSERT INTO tblDay VALUES (519,1180828800,3,6,2007,7);
INSERT INTO tblDay VALUES (520,1180915200,4,6,2007,1);
INSERT INTO tblDay VALUES (521,1181001600,5,6,2007,2);
INSERT INTO tblDay VALUES (522,1181088000,6,6,2007,3);
INSERT INTO tblDay VALUES (523,1181174400,7,6,2007,4);
INSERT INTO tblDay VALUES (524,1181260800,8,6,2007,5);
INSERT INTO tblDay VALUES (525,1181347200,9,6,2007,6);
INSERT INTO tblDay VALUES (526,1181433600,10,6,2007,7);
INSERT INTO tblDay VALUES (527,1181520000,11,6,2007,1);
INSERT INTO tblDay VALUES (528,1181606400,12,6,2007,2);
INSERT INTO tblDay VALUES (529,1181692800,13,6,2007,3);
INSERT INTO tblDay VALUES (530,1181779200,14,6,2007,4);
INSERT INTO tblDay VALUES (531,1181865600,15,6,2007,5);
INSERT INTO tblDay VALUES (532,1181952000,16,6,2007,6);
INSERT INTO tblDay VALUES (533,1182038400,17,6,2007,7);
INSERT INTO tblDay VALUES (534,1182124800,18,6,2007,1);
INSERT INTO tblDay VALUES (535,1182211200,19,6,2007,2);
INSERT INTO tblDay VALUES (536,1182297600,20,6,2007,3);
INSERT INTO tblDay VALUES (537,1182384000,21,6,2007,4);
INSERT INTO tblDay VALUES (538,1182470400,22,6,2007,5);
INSERT INTO tblDay VALUES (539,1182556800,23,6,2007,6);
INSERT INTO tblDay VALUES (540,1182643200,24,6,2007,7);
INSERT INTO tblDay VALUES (541,1182729600,25,6,2007,1);
INSERT INTO tblDay VALUES (542,1182816000,26,6,2007,2);
INSERT INTO tblDay VALUES (543,1182902400,27,6,2007,3);
INSERT INTO tblDay VALUES (544,1182988800,28,6,2007,4);
INSERT INTO tblDay VALUES (545,1183075200,29,6,2007,5);
INSERT INTO tblDay VALUES (546,1183161600,30,6,2007,6);
INSERT INTO tblDay VALUES (547,1183248000,1,7,2007,7);
INSERT INTO tblDay VALUES (548,1183334400,2,7,2007,1);
INSERT INTO tblDay VALUES (549,1183420800,3,7,2007,2);
INSERT INTO tblDay VALUES (550,1183507200,4,7,2007,3);
INSERT INTO tblDay VALUES (551,1183593600,5,7,2007,4);
INSERT INTO tblDay VALUES (552,1183680000,6,7,2007,5);
INSERT INTO tblDay VALUES (553,1183766400,7,7,2007,6);
INSERT INTO tblDay VALUES (554,1183852800,8,7,2007,7);
INSERT INTO tblDay VALUES (555,1183939200,9,7,2007,1);
INSERT INTO tblDay VALUES (556,1184025600,10,7,2007,2);
INSERT INTO tblDay VALUES (557,1184112000,11,7,2007,3);
INSERT INTO tblDay VALUES (558,1184198400,12,7,2007,4);
INSERT INTO tblDay VALUES (559,1184284800,13,7,2007,5);
INSERT INTO tblDay VALUES (560,1184371200,14,7,2007,6);
INSERT INTO tblDay VALUES (561,1184457600,15,7,2007,7);
INSERT INTO tblDay VALUES (562,1184544000,16,7,2007,1);
INSERT INTO tblDay VALUES (563,1184630400,17,7,2007,2);
INSERT INTO tblDay VALUES (564,1184716800,18,7,2007,3);
INSERT INTO tblDay VALUES (565,1184803200,19,7,2007,4);
INSERT INTO tblDay VALUES (566,1184889600,20,7,2007,5);
INSERT INTO tblDay VALUES (567,1184976000,21,7,2007,6);
INSERT INTO tblDay VALUES (568,1185062400,22,7,2007,7);
INSERT INTO tblDay VALUES (569,1185148800,23,7,2007,1);
INSERT INTO tblDay VALUES (570,1185235200,24,7,2007,2);
INSERT INTO tblDay VALUES (571,1185321600,25,7,2007,3);
INSERT INTO tblDay VALUES (572,1185408000,26,7,2007,4);
INSERT INTO tblDay VALUES (573,1185494400,27,7,2007,5);
INSERT INTO tblDay VALUES (574,1185580800,28,7,2007,6);
INSERT INTO tblDay VALUES (575,1185667200,29,7,2007,7);
INSERT INTO tblDay VALUES (576,1185753600,30,7,2007,1);
INSERT INTO tblDay VALUES (577,1185840000,31,7,2007,2);
INSERT INTO tblDay VALUES (578,1185926400,1,8,2007,3);
INSERT INTO tblDay VALUES (579,1186012800,2,8,2007,4);
INSERT INTO tblDay VALUES (580,1186099200,3,8,2007,5);
INSERT INTO tblDay VALUES (581,1186185600,4,8,2007,6);
INSERT INTO tblDay VALUES (582,1186272000,5,8,2007,7);
INSERT INTO tblDay VALUES (583,1186358400,6,8,2007,1);
INSERT INTO tblDay VALUES (584,1186444800,7,8,2007,2);
INSERT INTO tblDay VALUES (585,1186531200,8,8,2007,3);
INSERT INTO tblDay VALUES (586,1186617600,9,8,2007,4);
INSERT INTO tblDay VALUES (587,1186704000,10,8,2007,5);
INSERT INTO tblDay VALUES (588,1186790400,11,8,2007,6);
INSERT INTO tblDay VALUES (589,1186876800,12,8,2007,7);
INSERT INTO tblDay VALUES (590,1186963200,13,8,2007,1);
INSERT INTO tblDay VALUES (591,1187049600,14,8,2007,2);
INSERT INTO tblDay VALUES (592,1187136000,15,8,2007,3);
INSERT INTO tblDay VALUES (593,1187222400,16,8,2007,4);
INSERT INTO tblDay VALUES (594,1187308800,17,8,2007,5);
INSERT INTO tblDay VALUES (595,1187395200,18,8,2007,6);
INSERT INTO tblDay VALUES (596,1187481600,19,8,2007,7);
INSERT INTO tblDay VALUES (597,1187568000,20,8,2007,1);
INSERT INTO tblDay VALUES (598,1187654400,21,8,2007,2);
INSERT INTO tblDay VALUES (599,1187740800,22,8,2007,3);
INSERT INTO tblDay VALUES (600,1187827200,23,8,2007,4);
INSERT INTO tblDay VALUES (601,1187913600,24,8,2007,5);
INSERT INTO tblDay VALUES (602,1188000000,25,8,2007,6);
INSERT INTO tblDay VALUES (603,1188086400,26,8,2007,7);
INSERT INTO tblDay VALUES (604,1188172800,27,8,2007,1);
INSERT INTO tblDay VALUES (605,1188259200,28,8,2007,2);
INSERT INTO tblDay VALUES (606,1188345600,29,8,2007,3);
INSERT INTO tblDay VALUES (607,1188432000,30,8,2007,4);
INSERT INTO tblDay VALUES (608,1188518400,31,8,2007,5);
INSERT INTO tblDay VALUES (609,1188604800,1,9,2007,6);
INSERT INTO tblDay VALUES (610,1188691200,2,9,2007,7);
INSERT INTO tblDay VALUES (611,1188777600,3,9,2007,1);
INSERT INTO tblDay VALUES (612,1188864000,4,9,2007,2);
INSERT INTO tblDay VALUES (613,1188950400,5,9,2007,3);
INSERT INTO tblDay VALUES (614,1189036800,6,9,2007,4);
INSERT INTO tblDay VALUES (615,1189123200,7,9,2007,5);
INSERT INTO tblDay VALUES (616,1189209600,8,9,2007,6);
INSERT INTO tblDay VALUES (617,1189296000,9,9,2007,7);
INSERT INTO tblDay VALUES (618,1189382400,10,9,2007,1);
INSERT INTO tblDay VALUES (619,1189468800,11,9,2007,2);
INSERT INTO tblDay VALUES (620,1189555200,12,9,2007,3);
INSERT INTO tblDay VALUES (621,1189641600,13,9,2007,4);
INSERT INTO tblDay VALUES (622,1189728000,14,9,2007,5);
INSERT INTO tblDay VALUES (623,1189814400,15,9,2007,6);
INSERT INTO tblDay VALUES (624,1189900800,16,9,2007,7);
INSERT INTO tblDay VALUES (625,1189987200,17,9,2007,1);
INSERT INTO tblDay VALUES (626,1190073600,18,9,2007,2);
INSERT INTO tblDay VALUES (627,1190160000,19,9,2007,3);
INSERT INTO tblDay VALUES (628,1190246400,20,9,2007,4);
INSERT INTO tblDay VALUES (629,1190332800,21,9,2007,5);
INSERT INTO tblDay VALUES (630,1190419200,22,9,2007,6);
INSERT INTO tblDay VALUES (631,1190505600,23,9,2007,7);
INSERT INTO tblDay VALUES (632,1190592000,24,9,2007,1);
INSERT INTO tblDay VALUES (633,1190678400,25,9,2007,2);
INSERT INTO tblDay VALUES (634,1190764800,26,9,2007,3);
INSERT INTO tblDay VALUES (635,1190851200,27,9,2007,4);
INSERT INTO tblDay VALUES (636,1190937600,28,9,2007,5);
INSERT INTO tblDay VALUES (637,1191024000,29,9,2007,6);
INSERT INTO tblDay VALUES (638,1191110400,30,9,2007,7);
INSERT INTO tblDay VALUES (639,1191196800,1,10,2007,1);
INSERT INTO tblDay VALUES (640,1191283200,2,10,2007,2);
INSERT INTO tblDay VALUES (641,1191369600,3,10,2007,3);
INSERT INTO tblDay VALUES (642,1191456000,4,10,2007,4);
INSERT INTO tblDay VALUES (643,1191542400,5,10,2007,5);
INSERT INTO tblDay VALUES (644,1191628800,6,10,2007,6);
INSERT INTO tblDay VALUES (645,1191715200,7,10,2007,7);
INSERT INTO tblDay VALUES (646,1191801600,8,10,2007,1);
INSERT INTO tblDay VALUES (647,1191888000,9,10,2007,2);
INSERT INTO tblDay VALUES (648,1191974400,10,10,2007,3);
INSERT INTO tblDay VALUES (649,1192060800,11,10,2007,4);
INSERT INTO tblDay VALUES (650,1192147200,12,10,2007,5);
INSERT INTO tblDay VALUES (651,1192233600,13,10,2007,6);
INSERT INTO tblDay VALUES (652,1192320000,14,10,2007,7);
INSERT INTO tblDay VALUES (653,1192406400,15,10,2007,1);
INSERT INTO tblDay VALUES (654,1192492800,16,10,2007,2);
INSERT INTO tblDay VALUES (655,1192579200,17,10,2007,3);
INSERT INTO tblDay VALUES (656,1192665600,18,10,2007,4);
INSERT INTO tblDay VALUES (657,1192752000,19,10,2007,5);
INSERT INTO tblDay VALUES (658,1192838400,20,10,2007,6);
INSERT INTO tblDay VALUES (659,1192924800,21,10,2007,7);
INSERT INTO tblDay VALUES (660,1193011200,22,10,2007,1);
INSERT INTO tblDay VALUES (661,1193097600,23,10,2007,2);
INSERT INTO tblDay VALUES (662,1193184000,24,10,2007,3);
INSERT INTO tblDay VALUES (663,1193270400,25,10,2007,4);
INSERT INTO tblDay VALUES (664,1193356800,26,10,2007,5);
INSERT INTO tblDay VALUES (665,1193443200,27,10,2007,6);
INSERT INTO tblDay VALUES (666,1193529600,28,10,2007,7);
INSERT INTO tblDay VALUES (667,1193616000,29,10,2007,1);
INSERT INTO tblDay VALUES (668,1193702400,30,10,2007,2);
INSERT INTO tblDay VALUES (669,1193788800,31,10,2007,3);
INSERT INTO tblDay VALUES (670,1193875200,1,11,2007,4);
INSERT INTO tblDay VALUES (671,1193961600,2,11,2007,5);
INSERT INTO tblDay VALUES (672,1194048000,3,11,2007,6);
INSERT INTO tblDay VALUES (673,1194134400,4,11,2007,7);
INSERT INTO tblDay VALUES (674,1194220800,5,11,2007,1);
INSERT INTO tblDay VALUES (675,1194307200,6,11,2007,2);
INSERT INTO tblDay VALUES (676,1194393600,7,11,2007,3);
INSERT INTO tblDay VALUES (677,1194480000,8,11,2007,4);
INSERT INTO tblDay VALUES (678,1194566400,9,11,2007,5);
INSERT INTO tblDay VALUES (679,1194652800,10,11,2007,6);
INSERT INTO tblDay VALUES (680,1194739200,11,11,2007,7);
INSERT INTO tblDay VALUES (681,1194825600,12,11,2007,1);
INSERT INTO tblDay VALUES (682,1194912000,13,11,2007,2);
INSERT INTO tblDay VALUES (683,1194998400,14,11,2007,3);
INSERT INTO tblDay VALUES (684,1195084800,15,11,2007,4);
INSERT INTO tblDay VALUES (685,1195171200,16,11,2007,5);
INSERT INTO tblDay VALUES (686,1195257600,17,11,2007,6);
INSERT INTO tblDay VALUES (687,1195344000,18,11,2007,7);
INSERT INTO tblDay VALUES (688,1195430400,19,11,2007,1);
INSERT INTO tblDay VALUES (689,1195516800,20,11,2007,2);
INSERT INTO tblDay VALUES (690,1195603200,21,11,2007,3);
INSERT INTO tblDay VALUES (691,1195689600,22,11,2007,4);
INSERT INTO tblDay VALUES (692,1195776000,23,11,2007,5);
INSERT INTO tblDay VALUES (693,1195862400,24,11,2007,6);
INSERT INTO tblDay VALUES (694,1195948800,25,11,2007,7);
INSERT INTO tblDay VALUES (695,1196035200,26,11,2007,1);
INSERT INTO tblDay VALUES (696,1196121600,27,11,2007,2);
INSERT INTO tblDay VALUES (697,1196208000,28,11,2007,3);
INSERT INTO tblDay VALUES (698,1196294400,29,11,2007,4);
INSERT INTO tblDay VALUES (699,1196380800,30,11,2007,5);
INSERT INTO tblDay VALUES (700,1196467200,1,12,2007,6);
INSERT INTO tblDay VALUES (701,1196553600,2,12,2007,7);
INSERT INTO tblDay VALUES (702,1196640000,3,12,2007,1);
INSERT INTO tblDay VALUES (703,1196726400,4,12,2007,2);
INSERT INTO tblDay VALUES (704,1196812800,5,12,2007,3);
INSERT INTO tblDay VALUES (705,1196899200,6,12,2007,4);
INSERT INTO tblDay VALUES (706,1196985600,7,12,2007,5);
INSERT INTO tblDay VALUES (707,1197072000,8,12,2007,6);
INSERT INTO tblDay VALUES (708,1197158400,9,12,2007,7);
INSERT INTO tblDay VALUES (709,1197244800,10,12,2007,1);
INSERT INTO tblDay VALUES (710,1197331200,11,12,2007,2);
INSERT INTO tblDay VALUES (711,1197417600,12,12,2007,3);
INSERT INTO tblDay VALUES (712,1197504000,13,12,2007,4);
INSERT INTO tblDay VALUES (713,1197590400,14,12,2007,5);
INSERT INTO tblDay VALUES (714,1197676800,15,12,2007,6);
INSERT INTO tblDay VALUES (715,1197763200,16,12,2007,7);
INSERT INTO tblDay VALUES (716,1197849600,17,12,2007,1);
INSERT INTO tblDay VALUES (717,1197936000,18,12,2007,2);
INSERT INTO tblDay VALUES (718,1198022400,19,12,2007,3);
INSERT INTO tblDay VALUES (719,1198108800,20,12,2007,4);
INSERT INTO tblDay VALUES (720,1198195200,21,12,2007,5);
INSERT INTO tblDay VALUES (721,1198281600,22,12,2007,6);
INSERT INTO tblDay VALUES (722,1198368000,23,12,2007,7);
INSERT INTO tblDay VALUES (723,1198454400,24,12,2007,1);
INSERT INTO tblDay VALUES (724,1198540800,25,12,2007,2);
INSERT INTO tblDay VALUES (725,1198627200,26,12,2007,3);
INSERT INTO tblDay VALUES (726,1198713600,27,12,2007,4);
INSERT INTO tblDay VALUES (727,1198800000,28,12,2007,5);
INSERT INTO tblDay VALUES (728,1198886400,29,12,2007,6);
INSERT INTO tblDay VALUES (729,1198972800,30,12,2007,7);
INSERT INTO tblDay VALUES (730,1199059200,31,12,2007,1);
INSERT INTO tblDay VALUES (731,1199145600,1,1,2008,2);
INSERT INTO tblDay VALUES (732,1199232000,2,1,2008,3);
INSERT INTO tblDay VALUES (733,1199318400,3,1,2008,4);
INSERT INTO tblDay VALUES (734,1199404800,4,1,2008,5);
INSERT INTO tblDay VALUES (735,1199491200,5,1,2008,6);
INSERT INTO tblDay VALUES (736,1199577600,6,1,2008,7);
INSERT INTO tblDay VALUES (737,1199664000,7,1,2008,1);
INSERT INTO tblDay VALUES (738,1199750400,8,1,2008,2);
INSERT INTO tblDay VALUES (739,1199836800,9,1,2008,3);
INSERT INTO tblDay VALUES (740,1199923200,10,1,2008,4);
INSERT INTO tblDay VALUES (741,1200009600,11,1,2008,5);
INSERT INTO tblDay VALUES (742,1200096000,12,1,2008,6);
INSERT INTO tblDay VALUES (743,1200182400,13,1,2008,7);
INSERT INTO tblDay VALUES (744,1200268800,14,1,2008,1);
INSERT INTO tblDay VALUES (745,1200355200,15,1,2008,2);
INSERT INTO tblDay VALUES (746,1200441600,16,1,2008,3);
INSERT INTO tblDay VALUES (747,1200528000,17,1,2008,4);
INSERT INTO tblDay VALUES (748,1200614400,18,1,2008,5);
INSERT INTO tblDay VALUES (749,1200700800,19,1,2008,6);
INSERT INTO tblDay VALUES (750,1200787200,20,1,2008,7);
INSERT INTO tblDay VALUES (751,1200873600,21,1,2008,1);
INSERT INTO tblDay VALUES (752,1200960000,22,1,2008,2);
INSERT INTO tblDay VALUES (753,1201046400,23,1,2008,3);
INSERT INTO tblDay VALUES (754,1201132800,24,1,2008,4);
INSERT INTO tblDay VALUES (755,1201219200,25,1,2008,5);
INSERT INTO tblDay VALUES (756,1201305600,26,1,2008,6);
INSERT INTO tblDay VALUES (757,1201392000,27,1,2008,7);
INSERT INTO tblDay VALUES (758,1201478400,28,1,2008,1);
INSERT INTO tblDay VALUES (759,1201564800,29,1,2008,2);
INSERT INTO tblDay VALUES (760,1201651200,30,1,2008,3);
INSERT INTO tblDay VALUES (761,1201737600,31,1,2008,4);
INSERT INTO tblDay VALUES (762,1201824000,1,2,2008,5);
INSERT INTO tblDay VALUES (763,1201910400,2,2,2008,6);
INSERT INTO tblDay VALUES (764,1201996800,3,2,2008,7);
INSERT INTO tblDay VALUES (765,1202083200,4,2,2008,1);
INSERT INTO tblDay VALUES (766,1202169600,5,2,2008,2);
INSERT INTO tblDay VALUES (767,1202256000,6,2,2008,3);
INSERT INTO tblDay VALUES (768,1202342400,7,2,2008,4);
INSERT INTO tblDay VALUES (769,1202428800,8,2,2008,5);
INSERT INTO tblDay VALUES (770,1202515200,9,2,2008,6);
INSERT INTO tblDay VALUES (771,1202601600,10,2,2008,7);
INSERT INTO tblDay VALUES (772,1202688000,11,2,2008,1);
INSERT INTO tblDay VALUES (773,1202774400,12,2,2008,2);
INSERT INTO tblDay VALUES (774,1202860800,13,2,2008,3);
INSERT INTO tblDay VALUES (775,1202947200,14,2,2008,4);
INSERT INTO tblDay VALUES (776,1203033600,15,2,2008,5);
INSERT INTO tblDay VALUES (777,1203120000,16,2,2008,6);
INSERT INTO tblDay VALUES (778,1203206400,17,2,2008,7);
INSERT INTO tblDay VALUES (779,1203292800,18,2,2008,1);
INSERT INTO tblDay VALUES (780,1203379200,19,2,2008,2);
INSERT INTO tblDay VALUES (781,1203465600,20,2,2008,3);
INSERT INTO tblDay VALUES (782,1203552000,21,2,2008,4);
INSERT INTO tblDay VALUES (783,1203638400,22,2,2008,5);
INSERT INTO tblDay VALUES (784,1203724800,23,2,2008,6);
INSERT INTO tblDay VALUES (785,1203811200,24,2,2008,7);
INSERT INTO tblDay VALUES (786,1203897600,25,2,2008,1);
INSERT INTO tblDay VALUES (787,1203984000,26,2,2008,2);
INSERT INTO tblDay VALUES (788,1204070400,27,2,2008,3);
INSERT INTO tblDay VALUES (789,1204156800,28,2,2008,4);
INSERT INTO tblDay VALUES (790,1204243200,29,2,2008,5);
INSERT INTO tblDay VALUES (791,1204329600,1,3,2008,6);
INSERT INTO tblDay VALUES (792,1204416000,2,3,2008,7);
INSERT INTO tblDay VALUES (793,1204502400,3,3,2008,1);
INSERT INTO tblDay VALUES (794,1204588800,4,3,2008,2);
INSERT INTO tblDay VALUES (795,1204675200,5,3,2008,3);
INSERT INTO tblDay VALUES (796,1204761600,6,3,2008,4);
INSERT INTO tblDay VALUES (797,1204848000,7,3,2008,5);
INSERT INTO tblDay VALUES (798,1204934400,8,3,2008,6);
INSERT INTO tblDay VALUES (799,1205020800,9,3,2008,7);
INSERT INTO tblDay VALUES (800,1205107200,10,3,2008,1);
INSERT INTO tblDay VALUES (801,1205193600,11,3,2008,2);
INSERT INTO tblDay VALUES (802,1205280000,12,3,2008,3);
INSERT INTO tblDay VALUES (803,1205366400,13,3,2008,4);
INSERT INTO tblDay VALUES (804,1205452800,14,3,2008,5);
INSERT INTO tblDay VALUES (805,1205539200,15,3,2008,6);
INSERT INTO tblDay VALUES (806,1205625600,16,3,2008,7);
INSERT INTO tblDay VALUES (807,1205712000,17,3,2008,1);
INSERT INTO tblDay VALUES (808,1205798400,18,3,2008,2);
INSERT INTO tblDay VALUES (809,1205884800,19,3,2008,3);
INSERT INTO tblDay VALUES (810,1205971200,20,3,2008,4);
INSERT INTO tblDay VALUES (811,1206057600,21,3,2008,5);
INSERT INTO tblDay VALUES (812,1206144000,22,3,2008,6);
INSERT INTO tblDay VALUES (813,1206230400,23,3,2008,7);
INSERT INTO tblDay VALUES (814,1206316800,24,3,2008,1);
INSERT INTO tblDay VALUES (815,1206403200,25,3,2008,2);
INSERT INTO tblDay VALUES (816,1206489600,26,3,2008,3);
INSERT INTO tblDay VALUES (817,1206576000,27,3,2008,4);
INSERT INTO tblDay VALUES (818,1206662400,28,3,2008,5);
INSERT INTO tblDay VALUES (819,1206748800,29,3,2008,6);
INSERT INTO tblDay VALUES (820,1206835200,30,3,2008,7);
INSERT INTO tblDay VALUES (821,1206921600,31,3,2008,1);
INSERT INTO tblDay VALUES (822,1207008000,1,4,2008,2);
INSERT INTO tblDay VALUES (823,1207094400,2,4,2008,3);
INSERT INTO tblDay VALUES (824,1207180800,3,4,2008,4);
INSERT INTO tblDay VALUES (825,1207267200,4,4,2008,5);
INSERT INTO tblDay VALUES (826,1207353600,5,4,2008,6);
INSERT INTO tblDay VALUES (827,1207440000,6,4,2008,7);
INSERT INTO tblDay VALUES (828,1207526400,7,4,2008,1);
INSERT INTO tblDay VALUES (829,1207612800,8,4,2008,2);
INSERT INTO tblDay VALUES (830,1207699200,9,4,2008,3);
INSERT INTO tblDay VALUES (831,1207785600,10,4,2008,4);
INSERT INTO tblDay VALUES (832,1207872000,11,4,2008,5);
INSERT INTO tblDay VALUES (833,1207958400,12,4,2008,6);
INSERT INTO tblDay VALUES (834,1208044800,13,4,2008,7);
INSERT INTO tblDay VALUES (835,1208131200,14,4,2008,1);
INSERT INTO tblDay VALUES (836,1208217600,15,4,2008,2);
INSERT INTO tblDay VALUES (837,1208304000,16,4,2008,3);
INSERT INTO tblDay VALUES (838,1208390400,17,4,2008,4);
INSERT INTO tblDay VALUES (839,1208476800,18,4,2008,5);
INSERT INTO tblDay VALUES (840,1208563200,19,4,2008,6);
INSERT INTO tblDay VALUES (841,1208649600,20,4,2008,7);
INSERT INTO tblDay VALUES (842,1208736000,21,4,2008,1);
INSERT INTO tblDay VALUES (843,1208822400,22,4,2008,2);
INSERT INTO tblDay VALUES (844,1208908800,23,4,2008,3);
INSERT INTO tblDay VALUES (845,1208995200,24,4,2008,4);
INSERT INTO tblDay VALUES (846,1209081600,25,4,2008,5);
INSERT INTO tblDay VALUES (847,1209168000,26,4,2008,6);
INSERT INTO tblDay VALUES (848,1209254400,27,4,2008,7);
INSERT INTO tblDay VALUES (849,1209340800,28,4,2008,1);
INSERT INTO tblDay VALUES (850,1209427200,29,4,2008,2);
INSERT INTO tblDay VALUES (851,1209513600,30,4,2008,3);
INSERT INTO tblDay VALUES (852,1209600000,1,5,2008,4);
INSERT INTO tblDay VALUES (853,1209686400,2,5,2008,5);
INSERT INTO tblDay VALUES (854,1209772800,3,5,2008,6);
INSERT INTO tblDay VALUES (855,1209859200,4,5,2008,7);
INSERT INTO tblDay VALUES (856,1209945600,5,5,2008,1);
INSERT INTO tblDay VALUES (857,1210032000,6,5,2008,2);
INSERT INTO tblDay VALUES (858,1210118400,7,5,2008,3);
INSERT INTO tblDay VALUES (859,1210204800,8,5,2008,4);
INSERT INTO tblDay VALUES (860,1210291200,9,5,2008,5);
INSERT INTO tblDay VALUES (861,1210377600,10,5,2008,6);
INSERT INTO tblDay VALUES (862,1210464000,11,5,2008,7);
INSERT INTO tblDay VALUES (863,1210550400,12,5,2008,1);
INSERT INTO tblDay VALUES (864,1210636800,13,5,2008,2);
INSERT INTO tblDay VALUES (865,1210723200,14,5,2008,3);
INSERT INTO tblDay VALUES (866,1210809600,15,5,2008,4);
INSERT INTO tblDay VALUES (867,1210896000,16,5,2008,5);
INSERT INTO tblDay VALUES (868,1210982400,17,5,2008,6);
INSERT INTO tblDay VALUES (869,1211068800,18,5,2008,7);
INSERT INTO tblDay VALUES (870,1211155200,19,5,2008,1);
INSERT INTO tblDay VALUES (871,1211241600,20,5,2008,2);
INSERT INTO tblDay VALUES (872,1211328000,21,5,2008,3);
INSERT INTO tblDay VALUES (873,1211414400,22,5,2008,4);
INSERT INTO tblDay VALUES (874,1211500800,23,5,2008,5);
INSERT INTO tblDay VALUES (875,1211587200,24,5,2008,6);
INSERT INTO tblDay VALUES (876,1211673600,25,5,2008,7);
INSERT INTO tblDay VALUES (877,1211760000,26,5,2008,1);
INSERT INTO tblDay VALUES (878,1211846400,27,5,2008,2);
INSERT INTO tblDay VALUES (879,1211932800,28,5,2008,3);
INSERT INTO tblDay VALUES (880,1212019200,29,5,2008,4);
INSERT INTO tblDay VALUES (881,1212105600,30,5,2008,5);
INSERT INTO tblDay VALUES (882,1212192000,31,5,2008,6);
INSERT INTO tblDay VALUES (883,1212278400,1,6,2008,7);
INSERT INTO tblDay VALUES (884,1212364800,2,6,2008,1);
INSERT INTO tblDay VALUES (885,1212451200,3,6,2008,2);
INSERT INTO tblDay VALUES (886,1212537600,4,6,2008,3);
INSERT INTO tblDay VALUES (887,1212624000,5,6,2008,4);
INSERT INTO tblDay VALUES (888,1212710400,6,6,2008,5);
INSERT INTO tblDay VALUES (889,1212796800,7,6,2008,6);
INSERT INTO tblDay VALUES (890,1212883200,8,6,2008,7);
INSERT INTO tblDay VALUES (891,1212969600,9,6,2008,1);
INSERT INTO tblDay VALUES (892,1213056000,10,6,2008,2);
INSERT INTO tblDay VALUES (893,1213142400,11,6,2008,3);
INSERT INTO tblDay VALUES (894,1213228800,12,6,2008,4);
INSERT INTO tblDay VALUES (895,1213315200,13,6,2008,5);
INSERT INTO tblDay VALUES (896,1213401600,14,6,2008,6);
INSERT INTO tblDay VALUES (897,1213488000,15,6,2008,7);
INSERT INTO tblDay VALUES (898,1213574400,16,6,2008,1);
INSERT INTO tblDay VALUES (899,1213660800,17,6,2008,2);
INSERT INTO tblDay VALUES (900,1213747200,18,6,2008,3);
INSERT INTO tblDay VALUES (901,1213833600,19,6,2008,4);
INSERT INTO tblDay VALUES (902,1213920000,20,6,2008,5);
INSERT INTO tblDay VALUES (903,1214006400,21,6,2008,6);
INSERT INTO tblDay VALUES (904,1214092800,22,6,2008,7);
INSERT INTO tblDay VALUES (905,1214179200,23,6,2008,1);
INSERT INTO tblDay VALUES (906,1214265600,24,6,2008,2);
INSERT INTO tblDay VALUES (907,1214352000,25,6,2008,3);
INSERT INTO tblDay VALUES (908,1214438400,26,6,2008,4);
INSERT INTO tblDay VALUES (909,1214524800,27,6,2008,5);
INSERT INTO tblDay VALUES (910,1214611200,28,6,2008,6);
INSERT INTO tblDay VALUES (911,1214697600,29,6,2008,7);
INSERT INTO tblDay VALUES (912,1214784000,30,6,2008,1);
INSERT INTO tblDay VALUES (913,1214870400,1,7,2008,2);
INSERT INTO tblDay VALUES (914,1214956800,2,7,2008,3);
INSERT INTO tblDay VALUES (915,1215043200,3,7,2008,4);
INSERT INTO tblDay VALUES (916,1215129600,4,7,2008,5);
INSERT INTO tblDay VALUES (917,1215216000,5,7,2008,6);
INSERT INTO tblDay VALUES (918,1215302400,6,7,2008,7);
INSERT INTO tblDay VALUES (919,1215388800,7,7,2008,1);
INSERT INTO tblDay VALUES (920,1215475200,8,7,2008,2);
INSERT INTO tblDay VALUES (921,1215561600,9,7,2008,3);
INSERT INTO tblDay VALUES (922,1215648000,10,7,2008,4);
INSERT INTO tblDay VALUES (923,1215734400,11,7,2008,5);
INSERT INTO tblDay VALUES (924,1215820800,12,7,2008,6);
INSERT INTO tblDay VALUES (925,1215907200,13,7,2008,7);
INSERT INTO tblDay VALUES (926,1215993600,14,7,2008,1);
INSERT INTO tblDay VALUES (927,1216080000,15,7,2008,2);
INSERT INTO tblDay VALUES (928,1216166400,16,7,2008,3);
INSERT INTO tblDay VALUES (929,1216252800,17,7,2008,4);
INSERT INTO tblDay VALUES (930,1216339200,18,7,2008,5);
INSERT INTO tblDay VALUES (931,1216425600,19,7,2008,6);
INSERT INTO tblDay VALUES (932,1216512000,20,7,2008,7);
INSERT INTO tblDay VALUES (933,1216598400,21,7,2008,1);
INSERT INTO tblDay VALUES (934,1216684800,22,7,2008,2);
INSERT INTO tblDay VALUES (935,1216771200,23,7,2008,3);
INSERT INTO tblDay VALUES (936,1216857600,24,7,2008,4);
INSERT INTO tblDay VALUES (937,1216944000,25,7,2008,5);
INSERT INTO tblDay VALUES (938,1217030400,26,7,2008,6);
INSERT INTO tblDay VALUES (939,1217116800,27,7,2008,7);
INSERT INTO tblDay VALUES (940,1217203200,28,7,2008,1);
INSERT INTO tblDay VALUES (941,1217289600,29,7,2008,2);
INSERT INTO tblDay VALUES (942,1217376000,30,7,2008,3);
INSERT INTO tblDay VALUES (943,1217462400,31,7,2008,4);
INSERT INTO tblDay VALUES (944,1217548800,1,8,2008,5);
INSERT INTO tblDay VALUES (945,1217635200,2,8,2008,6);
INSERT INTO tblDay VALUES (946,1217721600,3,8,2008,7);
INSERT INTO tblDay VALUES (947,1217808000,4,8,2008,1);
INSERT INTO tblDay VALUES (948,1217894400,5,8,2008,2);
INSERT INTO tblDay VALUES (949,1217980800,6,8,2008,3);
INSERT INTO tblDay VALUES (950,1218067200,7,8,2008,4);
INSERT INTO tblDay VALUES (951,1218153600,8,8,2008,5);
INSERT INTO tblDay VALUES (952,1218240000,9,8,2008,6);
INSERT INTO tblDay VALUES (953,1218326400,10,8,2008,7);
INSERT INTO tblDay VALUES (954,1218412800,11,8,2008,1);
INSERT INTO tblDay VALUES (955,1218499200,12,8,2008,2);
INSERT INTO tblDay VALUES (956,1218585600,13,8,2008,3);
INSERT INTO tblDay VALUES (957,1218672000,14,8,2008,4);
INSERT INTO tblDay VALUES (958,1218758400,15,8,2008,5);
INSERT INTO tblDay VALUES (959,1218844800,16,8,2008,6);
INSERT INTO tblDay VALUES (960,1218931200,17,8,2008,7);
INSERT INTO tblDay VALUES (961,1219017600,18,8,2008,1);
INSERT INTO tblDay VALUES (962,1219104000,19,8,2008,2);
INSERT INTO tblDay VALUES (963,1219190400,20,8,2008,3);
INSERT INTO tblDay VALUES (964,1219276800,21,8,2008,4);
INSERT INTO tblDay VALUES (965,1219363200,22,8,2008,5);
INSERT INTO tblDay VALUES (966,1219449600,23,8,2008,6);
INSERT INTO tblDay VALUES (967,1219536000,24,8,2008,7);
INSERT INTO tblDay VALUES (968,1219622400,25,8,2008,1);
INSERT INTO tblDay VALUES (969,1219708800,26,8,2008,2);
INSERT INTO tblDay VALUES (970,1219795200,27,8,2008,3);
INSERT INTO tblDay VALUES (971,1219881600,28,8,2008,4);
INSERT INTO tblDay VALUES (972,1219968000,29,8,2008,5);
INSERT INTO tblDay VALUES (973,1220054400,30,8,2008,6);
INSERT INTO tblDay VALUES (974,1220140800,31,8,2008,7);
INSERT INTO tblDay VALUES (975,1220227200,1,9,2008,1);
INSERT INTO tblDay VALUES (976,1220313600,2,9,2008,2);
INSERT INTO tblDay VALUES (977,1220400000,3,9,2008,3);
INSERT INTO tblDay VALUES (978,1220486400,4,9,2008,4);
INSERT INTO tblDay VALUES (979,1220572800,5,9,2008,5);
INSERT INTO tblDay VALUES (980,1220659200,6,9,2008,6);
INSERT INTO tblDay VALUES (981,1220745600,7,9,2008,7);
INSERT INTO tblDay VALUES (982,1220832000,8,9,2008,1);
INSERT INTO tblDay VALUES (983,1220918400,9,9,2008,2);
INSERT INTO tblDay VALUES (984,1221004800,10,9,2008,3);
INSERT INTO tblDay VALUES (985,1221091200,11,9,2008,4);
INSERT INTO tblDay VALUES (986,1221177600,12,9,2008,5);
INSERT INTO tblDay VALUES (987,1221264000,13,9,2008,6);
INSERT INTO tblDay VALUES (988,1221350400,14,9,2008,7);
INSERT INTO tblDay VALUES (989,1221436800,15,9,2008,1);
INSERT INTO tblDay VALUES (990,1221523200,16,9,2008,2);
INSERT INTO tblDay VALUES (991,1221609600,17,9,2008,3);
INSERT INTO tblDay VALUES (992,1221696000,18,9,2008,4);
INSERT INTO tblDay VALUES (993,1221782400,19,9,2008,5);
INSERT INTO tblDay VALUES (994,1221868800,20,9,2008,6);
INSERT INTO tblDay VALUES (995,1221955200,21,9,2008,7);
INSERT INTO tblDay VALUES (996,1222041600,22,9,2008,1);
INSERT INTO tblDay VALUES (997,1222128000,23,9,2008,2);
INSERT INTO tblDay VALUES (998,1222214400,24,9,2008,3);
INSERT INTO tblDay VALUES (999,1222300800,25,9,2008,4);
INSERT INTO tblDay VALUES (1000,1222387200,26,9,2008,5);
INSERT INTO tblDay VALUES (1001,1222473600,27,9,2008,6);
INSERT INTO tblDay VALUES (1002,1222560000,28,9,2008,7);
INSERT INTO tblDay VALUES (1003,1222646400,29,9,2008,1);
INSERT INTO tblDay VALUES (1004,1222732800,30,9,2008,2);
INSERT INTO tblDay VALUES (1005,1222819200,1,10,2008,3);
INSERT INTO tblDay VALUES (1006,1222905600,2,10,2008,4);
INSERT INTO tblDay VALUES (1007,1222992000,3,10,2008,5);
INSERT INTO tblDay VALUES (1008,1223078400,4,10,2008,6);
INSERT INTO tblDay VALUES (1009,1223164800,5,10,2008,7);
INSERT INTO tblDay VALUES (1010,1223251200,6,10,2008,1);
INSERT INTO tblDay VALUES (1011,1223337600,7,10,2008,2);
INSERT INTO tblDay VALUES (1012,1223424000,8,10,2008,3);
INSERT INTO tblDay VALUES (1013,1223510400,9,10,2008,4);
INSERT INTO tblDay VALUES (1014,1223596800,10,10,2008,5);
INSERT INTO tblDay VALUES (1015,1223683200,11,10,2008,6);
INSERT INTO tblDay VALUES (1016,1223769600,12,10,2008,7);
INSERT INTO tblDay VALUES (1017,1223856000,13,10,2008,1);
INSERT INTO tblDay VALUES (1018,1223942400,14,10,2008,2);
INSERT INTO tblDay VALUES (1019,1224028800,15,10,2008,3);
INSERT INTO tblDay VALUES (1020,1224115200,16,10,2008,4);
INSERT INTO tblDay VALUES (1021,1224201600,17,10,2008,5);
INSERT INTO tblDay VALUES (1022,1224288000,18,10,2008,6);
INSERT INTO tblDay VALUES (1023,1224374400,19,10,2008,7);
INSERT INTO tblDay VALUES (1024,1224460800,20,10,2008,1);
INSERT INTO tblDay VALUES (1025,1224547200,21,10,2008,2);
INSERT INTO tblDay VALUES (1026,1224633600,22,10,2008,3);
INSERT INTO tblDay VALUES (1027,1224720000,23,10,2008,4);
INSERT INTO tblDay VALUES (1028,1224806400,24,10,2008,5);
INSERT INTO tblDay VALUES (1029,1224892800,25,10,2008,6);
INSERT INTO tblDay VALUES (1030,1224979200,26,10,2008,7);
INSERT INTO tblDay VALUES (1031,1225065600,27,10,2008,1);
INSERT INTO tblDay VALUES (1032,1225152000,28,10,2008,2);
INSERT INTO tblDay VALUES (1033,1225238400,29,10,2008,3);
INSERT INTO tblDay VALUES (1034,1225324800,30,10,2008,4);
INSERT INTO tblDay VALUES (1035,1225411200,31,10,2008,5);
INSERT INTO tblDay VALUES (1036,1225497600,1,11,2008,6);
INSERT INTO tblDay VALUES (1037,1225584000,2,11,2008,7);
INSERT INTO tblDay VALUES (1038,1225670400,3,11,2008,1);
INSERT INTO tblDay VALUES (1039,1225756800,4,11,2008,2);
INSERT INTO tblDay VALUES (1040,1225843200,5,11,2008,3);
INSERT INTO tblDay VALUES (1041,1225929600,6,11,2008,4);
INSERT INTO tblDay VALUES (1042,1226016000,7,11,2008,5);
INSERT INTO tblDay VALUES (1043,1226102400,8,11,2008,6);
INSERT INTO tblDay VALUES (1044,1226188800,9,11,2008,7);
INSERT INTO tblDay VALUES (1045,1226275200,10,11,2008,1);
INSERT INTO tblDay VALUES (1046,1226361600,11,11,2008,2);
INSERT INTO tblDay VALUES (1047,1226448000,12,11,2008,3);
INSERT INTO tblDay VALUES (1048,1226534400,13,11,2008,4);
INSERT INTO tblDay VALUES (1049,1226620800,14,11,2008,5);
INSERT INTO tblDay VALUES (1050,1226707200,15,11,2008,6);
INSERT INTO tblDay VALUES (1051,1226793600,16,11,2008,7);
INSERT INTO tblDay VALUES (1052,1226880000,17,11,2008,1);
INSERT INTO tblDay VALUES (1053,1226966400,18,11,2008,2);
INSERT INTO tblDay VALUES (1054,1227052800,19,11,2008,3);
INSERT INTO tblDay VALUES (1055,1227139200,20,11,2008,4);
INSERT INTO tblDay VALUES (1056,1227225600,21,11,2008,5);
INSERT INTO tblDay VALUES (1057,1227312000,22,11,2008,6);
INSERT INTO tblDay VALUES (1058,1227398400,23,11,2008,7);
INSERT INTO tblDay VALUES (1059,1227484800,24,11,2008,1);
INSERT INTO tblDay VALUES (1060,1227571200,25,11,2008,2);
INSERT INTO tblDay VALUES (1061,1227657600,26,11,2008,3);
INSERT INTO tblDay VALUES (1062,1227744000,27,11,2008,4);
INSERT INTO tblDay VALUES (1063,1227830400,28,11,2008,5);
INSERT INTO tblDay VALUES (1064,1227916800,29,11,2008,6);
INSERT INTO tblDay VALUES (1065,1228003200,30,11,2008,7);
INSERT INTO tblDay VALUES (1066,1228089600,1,12,2008,1);
INSERT INTO tblDay VALUES (1067,1228176000,2,12,2008,2);
INSERT INTO tblDay VALUES (1068,1228262400,3,12,2008,3);
INSERT INTO tblDay VALUES (1069,1228348800,4,12,2008,4);
INSERT INTO tblDay VALUES (1070,1228435200,5,12,2008,5);
INSERT INTO tblDay VALUES (1071,1228521600,6,12,2008,6);
INSERT INTO tblDay VALUES (1072,1228608000,7,12,2008,7);
INSERT INTO tblDay VALUES (1073,1228694400,8,12,2008,1);
INSERT INTO tblDay VALUES (1074,1228780800,9,12,2008,2);
INSERT INTO tblDay VALUES (1075,1228867200,10,12,2008,3);
INSERT INTO tblDay VALUES (1076,1228953600,11,12,2008,4);
INSERT INTO tblDay VALUES (1077,1229040000,12,12,2008,5);
INSERT INTO tblDay VALUES (1078,1229126400,13,12,2008,6);
INSERT INTO tblDay VALUES (1079,1229212800,14,12,2008,7);
INSERT INTO tblDay VALUES (1080,1229299200,15,12,2008,1);
INSERT INTO tblDay VALUES (1081,1229385600,16,12,2008,2);
INSERT INTO tblDay VALUES (1082,1229472000,17,12,2008,3);
INSERT INTO tblDay VALUES (1083,1229558400,18,12,2008,4);
INSERT INTO tblDay VALUES (1084,1229644800,19,12,2008,5);
INSERT INTO tblDay VALUES (1085,1229731200,20,12,2008,6);
INSERT INTO tblDay VALUES (1086,1229817600,21,12,2008,7);
INSERT INTO tblDay VALUES (1087,1229904000,22,12,2008,1);
INSERT INTO tblDay VALUES (1088,1229990400,23,12,2008,2);
INSERT INTO tblDay VALUES (1089,1230076800,24,12,2008,3);
INSERT INTO tblDay VALUES (1090,1230163200,25,12,2008,4);
INSERT INTO tblDay VALUES (1091,1230249600,26,12,2008,5);
INSERT INTO tblDay VALUES (1092,1230336000,27,12,2008,6);
INSERT INTO tblDay VALUES (1093,1230422400,28,12,2008,7);
INSERT INTO tblDay VALUES (1094,1230508800,29,12,2008,1);
INSERT INTO tblDay VALUES (1095,1230595200,30,12,2008,2);
INSERT INTO tblDay VALUES (1096,1230681600,31,12,2008,3);
INSERT INTO tblDay VALUES (1097,1230768000,1,1,2009,4);
INSERT INTO tblDay VALUES (1098,1230854400,2,1,2009,5);
INSERT INTO tblDay VALUES (1099,1230940800,3,1,2009,6);
INSERT INTO tblDay VALUES (1100,1231027200,4,1,2009,7);
INSERT INTO tblDay VALUES (1101,1231113600,5,1,2009,1);
INSERT INTO tblDay VALUES (1102,1231200000,6,1,2009,2);
INSERT INTO tblDay VALUES (1103,1231286400,7,1,2009,3);
INSERT INTO tblDay VALUES (1104,1231372800,8,1,2009,4);
INSERT INTO tblDay VALUES (1105,1231459200,9,1,2009,5);
INSERT INTO tblDay VALUES (1106,1231545600,10,1,2009,6);
INSERT INTO tblDay VALUES (1107,1231632000,11,1,2009,7);
INSERT INTO tblDay VALUES (1108,1231718400,12,1,2009,1);
INSERT INTO tblDay VALUES (1109,1231804800,13,1,2009,2);
INSERT INTO tblDay VALUES (1110,1231891200,14,1,2009,3);
INSERT INTO tblDay VALUES (1111,1231977600,15,1,2009,4);
INSERT INTO tblDay VALUES (1112,1232064000,16,1,2009,5);
INSERT INTO tblDay VALUES (1113,1232150400,17,1,2009,6);
INSERT INTO tblDay VALUES (1114,1232236800,18,1,2009,7);
INSERT INTO tblDay VALUES (1115,1232323200,19,1,2009,1);
INSERT INTO tblDay VALUES (1116,1232409600,20,1,2009,2);
INSERT INTO tblDay VALUES (1117,1232496000,21,1,2009,3);
INSERT INTO tblDay VALUES (1118,1232582400,22,1,2009,4);
INSERT INTO tblDay VALUES (1119,1232668800,23,1,2009,5);
INSERT INTO tblDay VALUES (1120,1232755200,24,1,2009,6);
INSERT INTO tblDay VALUES (1121,1232841600,25,1,2009,7);
INSERT INTO tblDay VALUES (1122,1232928000,26,1,2009,1);
INSERT INTO tblDay VALUES (1123,1233014400,27,1,2009,2);
INSERT INTO tblDay VALUES (1124,1233100800,28,1,2009,3);
INSERT INTO tblDay VALUES (1125,1233187200,29,1,2009,4);
INSERT INTO tblDay VALUES (1126,1233273600,30,1,2009,5);
INSERT INTO tblDay VALUES (1127,1233360000,31,1,2009,6);
INSERT INTO tblDay VALUES (1128,1233446400,1,2,2009,7);
INSERT INTO tblDay VALUES (1129,1233532800,2,2,2009,1);
INSERT INTO tblDay VALUES (1130,1233619200,3,2,2009,2);
INSERT INTO tblDay VALUES (1131,1233705600,4,2,2009,3);
INSERT INTO tblDay VALUES (1132,1233792000,5,2,2009,4);
INSERT INTO tblDay VALUES (1133,1233878400,6,2,2009,5);
INSERT INTO tblDay VALUES (1134,1233964800,7,2,2009,6);
INSERT INTO tblDay VALUES (1135,1234051200,8,2,2009,7);
INSERT INTO tblDay VALUES (1136,1234137600,9,2,2009,1);
INSERT INTO tblDay VALUES (1137,1234224000,10,2,2009,2);
INSERT INTO tblDay VALUES (1138,1234310400,11,2,2009,3);
INSERT INTO tblDay VALUES (1139,1234396800,12,2,2009,4);
INSERT INTO tblDay VALUES (1140,1234483200,13,2,2009,5);
INSERT INTO tblDay VALUES (1141,1234569600,14,2,2009,6);
INSERT INTO tblDay VALUES (1142,1234656000,15,2,2009,7);
INSERT INTO tblDay VALUES (1143,1234742400,16,2,2009,1);
INSERT INTO tblDay VALUES (1144,1234828800,17,2,2009,2);
INSERT INTO tblDay VALUES (1145,1234915200,18,2,2009,3);
INSERT INTO tblDay VALUES (1146,1235001600,19,2,2009,4);
INSERT INTO tblDay VALUES (1147,1235088000,20,2,2009,5);
INSERT INTO tblDay VALUES (1148,1235174400,21,2,2009,6);
INSERT INTO tblDay VALUES (1149,1235260800,22,2,2009,7);
INSERT INTO tblDay VALUES (1150,1235347200,23,2,2009,1);
INSERT INTO tblDay VALUES (1151,1235433600,24,2,2009,2);
INSERT INTO tblDay VALUES (1152,1235520000,25,2,2009,3);
INSERT INTO tblDay VALUES (1153,1235606400,26,2,2009,4);
INSERT INTO tblDay VALUES (1154,1235692800,27,2,2009,5);
INSERT INTO tblDay VALUES (1155,1235779200,28,2,2009,6);
INSERT INTO tblDay VALUES (1156,1235865600,1,3,2009,7);
INSERT INTO tblDay VALUES (1157,1235952000,2,3,2009,1);
INSERT INTO tblDay VALUES (1158,1236038400,3,3,2009,2);
INSERT INTO tblDay VALUES (1159,1236124800,4,3,2009,3);
INSERT INTO tblDay VALUES (1160,1236211200,5,3,2009,4);
INSERT INTO tblDay VALUES (1161,1236297600,6,3,2009,5);
INSERT INTO tblDay VALUES (1162,1236384000,7,3,2009,6);
INSERT INTO tblDay VALUES (1163,1236470400,8,3,2009,7);
INSERT INTO tblDay VALUES (1164,1236556800,9,3,2009,1);
INSERT INTO tblDay VALUES (1165,1236643200,10,3,2009,2);
INSERT INTO tblDay VALUES (1166,1236729600,11,3,2009,3);
INSERT INTO tblDay VALUES (1167,1236816000,12,3,2009,4);
INSERT INTO tblDay VALUES (1168,1236902400,13,3,2009,5);
INSERT INTO tblDay VALUES (1169,1236988800,14,3,2009,6);
INSERT INTO tblDay VALUES (1170,1237075200,15,3,2009,7);
INSERT INTO tblDay VALUES (1171,1237161600,16,3,2009,1);
INSERT INTO tblDay VALUES (1172,1237248000,17,3,2009,2);
INSERT INTO tblDay VALUES (1173,1237334400,18,3,2009,3);
INSERT INTO tblDay VALUES (1174,1237420800,19,3,2009,4);
INSERT INTO tblDay VALUES (1175,1237507200,20,3,2009,5);
INSERT INTO tblDay VALUES (1176,1237593600,21,3,2009,6);
INSERT INTO tblDay VALUES (1177,1237680000,22,3,2009,7);
INSERT INTO tblDay VALUES (1178,1237766400,23,3,2009,1);
INSERT INTO tblDay VALUES (1179,1237852800,24,3,2009,2);
INSERT INTO tblDay VALUES (1180,1237939200,25,3,2009,3);
INSERT INTO tblDay VALUES (1181,1238025600,26,3,2009,4);
INSERT INTO tblDay VALUES (1182,1238112000,27,3,2009,5);
INSERT INTO tblDay VALUES (1183,1238198400,28,3,2009,6);
INSERT INTO tblDay VALUES (1184,1238284800,29,3,2009,7);
INSERT INTO tblDay VALUES (1185,1238371200,30,3,2009,1);
INSERT INTO tblDay VALUES (1186,1238457600,31,3,2009,2);
INSERT INTO tblDay VALUES (1187,1238544000,1,4,2009,3);
INSERT INTO tblDay VALUES (1188,1238630400,2,4,2009,4);
INSERT INTO tblDay VALUES (1189,1238716800,3,4,2009,5);
INSERT INTO tblDay VALUES (1190,1238803200,4,4,2009,6);
INSERT INTO tblDay VALUES (1191,1238889600,5,4,2009,7);
INSERT INTO tblDay VALUES (1192,1238976000,6,4,2009,1);
INSERT INTO tblDay VALUES (1193,1239062400,7,4,2009,2);
INSERT INTO tblDay VALUES (1194,1239148800,8,4,2009,3);
INSERT INTO tblDay VALUES (1195,1239235200,9,4,2009,4);
INSERT INTO tblDay VALUES (1196,1239321600,10,4,2009,5);
INSERT INTO tblDay VALUES (1197,1239408000,11,4,2009,6);
INSERT INTO tblDay VALUES (1198,1239494400,12,4,2009,7);
INSERT INTO tblDay VALUES (1199,1239580800,13,4,2009,1);
INSERT INTO tblDay VALUES (1200,1239667200,14,4,2009,2);
INSERT INTO tblDay VALUES (1201,1239753600,15,4,2009,3);
INSERT INTO tblDay VALUES (1202,1239840000,16,4,2009,4);
INSERT INTO tblDay VALUES (1203,1239926400,17,4,2009,5);
INSERT INTO tblDay VALUES (1204,1240012800,18,4,2009,6);
INSERT INTO tblDay VALUES (1205,1240099200,19,4,2009,7);
INSERT INTO tblDay VALUES (1206,1240185600,20,4,2009,1);
INSERT INTO tblDay VALUES (1207,1240272000,21,4,2009,2);
INSERT INTO tblDay VALUES (1208,1240358400,22,4,2009,3);
INSERT INTO tblDay VALUES (1209,1240444800,23,4,2009,4);
INSERT INTO tblDay VALUES (1210,1240531200,24,4,2009,5);
INSERT INTO tblDay VALUES (1211,1240617600,25,4,2009,6);
INSERT INTO tblDay VALUES (1212,1240704000,26,4,2009,7);
INSERT INTO tblDay VALUES (1213,1240790400,27,4,2009,1);
INSERT INTO tblDay VALUES (1214,1240876800,28,4,2009,2);
INSERT INTO tblDay VALUES (1215,1240963200,29,4,2009,3);
INSERT INTO tblDay VALUES (1216,1241049600,30,4,2009,4);
INSERT INTO tblDay VALUES (1217,1241136000,1,5,2009,5);
INSERT INTO tblDay VALUES (1218,1241222400,2,5,2009,6);
INSERT INTO tblDay VALUES (1219,1241308800,3,5,2009,7);
INSERT INTO tblDay VALUES (1220,1241395200,4,5,2009,1);
INSERT INTO tblDay VALUES (1221,1241481600,5,5,2009,2);
INSERT INTO tblDay VALUES (1222,1241568000,6,5,2009,3);
INSERT INTO tblDay VALUES (1223,1241654400,7,5,2009,4);
INSERT INTO tblDay VALUES (1224,1241740800,8,5,2009,5);
INSERT INTO tblDay VALUES (1225,1241827200,9,5,2009,6);
INSERT INTO tblDay VALUES (1226,1241913600,10,5,2009,7);
INSERT INTO tblDay VALUES (1227,1242000000,11,5,2009,1);
INSERT INTO tblDay VALUES (1228,1242086400,12,5,2009,2);
INSERT INTO tblDay VALUES (1229,1242172800,13,5,2009,3);
INSERT INTO tblDay VALUES (1230,1242259200,14,5,2009,4);
INSERT INTO tblDay VALUES (1231,1242345600,15,5,2009,5);
INSERT INTO tblDay VALUES (1232,1242432000,16,5,2009,6);
INSERT INTO tblDay VALUES (1233,1242518400,17,5,2009,7);
INSERT INTO tblDay VALUES (1234,1242604800,18,5,2009,1);
INSERT INTO tblDay VALUES (1235,1242691200,19,5,2009,2);
INSERT INTO tblDay VALUES (1236,1242777600,20,5,2009,3);
INSERT INTO tblDay VALUES (1237,1242864000,21,5,2009,4);
INSERT INTO tblDay VALUES (1238,1242950400,22,5,2009,5);
INSERT INTO tblDay VALUES (1239,1243036800,23,5,2009,6);
INSERT INTO tblDay VALUES (1240,1243123200,24,5,2009,7);
INSERT INTO tblDay VALUES (1241,1243209600,25,5,2009,1);
INSERT INTO tblDay VALUES (1242,1243296000,26,5,2009,2);
INSERT INTO tblDay VALUES (1243,1243382400,27,5,2009,3);
INSERT INTO tblDay VALUES (1244,1243468800,28,5,2009,4);
INSERT INTO tblDay VALUES (1245,1243555200,29,5,2009,5);
INSERT INTO tblDay VALUES (1246,1243641600,30,5,2009,6);
INSERT INTO tblDay VALUES (1247,1243728000,31,5,2009,7);
INSERT INTO tblDay VALUES (1248,1243814400,1,6,2009,1);
INSERT INTO tblDay VALUES (1249,1243900800,2,6,2009,2);
INSERT INTO tblDay VALUES (1250,1243987200,3,6,2009,3);
INSERT INTO tblDay VALUES (1251,1244073600,4,6,2009,4);
INSERT INTO tblDay VALUES (1252,1244160000,5,6,2009,5);
INSERT INTO tblDay VALUES (1253,1244246400,6,6,2009,6);
INSERT INTO tblDay VALUES (1254,1244332800,7,6,2009,7);
INSERT INTO tblDay VALUES (1255,1244419200,8,6,2009,1);
INSERT INTO tblDay VALUES (1256,1244505600,9,6,2009,2);
INSERT INTO tblDay VALUES (1257,1244592000,10,6,2009,3);
INSERT INTO tblDay VALUES (1258,1244678400,11,6,2009,4);
INSERT INTO tblDay VALUES (1259,1244764800,12,6,2009,5);
INSERT INTO tblDay VALUES (1260,1244851200,13,6,2009,6);
INSERT INTO tblDay VALUES (1261,1244937600,14,6,2009,7);
INSERT INTO tblDay VALUES (1262,1245024000,15,6,2009,1);
INSERT INTO tblDay VALUES (1263,1245110400,16,6,2009,2);
INSERT INTO tblDay VALUES (1264,1245196800,17,6,2009,3);
INSERT INTO tblDay VALUES (1265,1245283200,18,6,2009,4);
INSERT INTO tblDay VALUES (1266,1245369600,19,6,2009,5);
INSERT INTO tblDay VALUES (1267,1245456000,20,6,2009,6);
INSERT INTO tblDay VALUES (1268,1245542400,21,6,2009,7);
INSERT INTO tblDay VALUES (1269,1245628800,22,6,2009,1);
INSERT INTO tblDay VALUES (1270,1245715200,23,6,2009,2);
INSERT INTO tblDay VALUES (1271,1245801600,24,6,2009,3);
INSERT INTO tblDay VALUES (1272,1245888000,25,6,2009,4);
INSERT INTO tblDay VALUES (1273,1245974400,26,6,2009,5);
INSERT INTO tblDay VALUES (1274,1246060800,27,6,2009,6);
INSERT INTO tblDay VALUES (1275,1246147200,28,6,2009,7);
INSERT INTO tblDay VALUES (1276,1246233600,29,6,2009,1);
INSERT INTO tblDay VALUES (1277,1246320000,30,6,2009,2);
INSERT INTO tblDay VALUES (1278,1246406400,1,7,2009,3);
INSERT INTO tblDay VALUES (1279,1246492800,2,7,2009,4);
INSERT INTO tblDay VALUES (1280,1246579200,3,7,2009,5);
INSERT INTO tblDay VALUES (1281,1246665600,4,7,2009,6);
INSERT INTO tblDay VALUES (1282,1246752000,5,7,2009,7);
INSERT INTO tblDay VALUES (1283,1246838400,6,7,2009,1);
INSERT INTO tblDay VALUES (1284,1246924800,7,7,2009,2);
INSERT INTO tblDay VALUES (1285,1247011200,8,7,2009,3);
INSERT INTO tblDay VALUES (1286,1247097600,9,7,2009,4);
INSERT INTO tblDay VALUES (1287,1247184000,10,7,2009,5);
INSERT INTO tblDay VALUES (1288,1247270400,11,7,2009,6);
INSERT INTO tblDay VALUES (1289,1247356800,12,7,2009,7);
INSERT INTO tblDay VALUES (1290,1247443200,13,7,2009,1);
INSERT INTO tblDay VALUES (1291,1247529600,14,7,2009,2);
INSERT INTO tblDay VALUES (1292,1247616000,15,7,2009,3);
INSERT INTO tblDay VALUES (1293,1247702400,16,7,2009,4);
INSERT INTO tblDay VALUES (1294,1247788800,17,7,2009,5);
INSERT INTO tblDay VALUES (1295,1247875200,18,7,2009,6);
INSERT INTO tblDay VALUES (1296,1247961600,19,7,2009,7);
INSERT INTO tblDay VALUES (1297,1248048000,20,7,2009,1);
INSERT INTO tblDay VALUES (1298,1248134400,21,7,2009,2);
INSERT INTO tblDay VALUES (1299,1248220800,22,7,2009,3);
INSERT INTO tblDay VALUES (1300,1248307200,23,7,2009,4);
INSERT INTO tblDay VALUES (1301,1248393600,24,7,2009,5);
INSERT INTO tblDay VALUES (1302,1248480000,25,7,2009,6);
INSERT INTO tblDay VALUES (1303,1248566400,26,7,2009,7);
INSERT INTO tblDay VALUES (1304,1248652800,27,7,2009,1);
INSERT INTO tblDay VALUES (1305,1248739200,28,7,2009,2);
INSERT INTO tblDay VALUES (1306,1248825600,29,7,2009,3);
INSERT INTO tblDay VALUES (1307,1248912000,30,7,2009,4);
INSERT INTO tblDay VALUES (1308,1248998400,31,7,2009,5);
INSERT INTO tblDay VALUES (1309,1249084800,1,8,2009,6);
INSERT INTO tblDay VALUES (1310,1249171200,2,8,2009,7);
INSERT INTO tblDay VALUES (1311,1249257600,3,8,2009,1);
INSERT INTO tblDay VALUES (1312,1249344000,4,8,2009,2);
INSERT INTO tblDay VALUES (1313,1249430400,5,8,2009,3);
INSERT INTO tblDay VALUES (1314,1249516800,6,8,2009,4);
INSERT INTO tblDay VALUES (1315,1249603200,7,8,2009,5);
INSERT INTO tblDay VALUES (1316,1249689600,8,8,2009,6);
INSERT INTO tblDay VALUES (1317,1249776000,9,8,2009,7);
INSERT INTO tblDay VALUES (1318,1249862400,10,8,2009,1);
INSERT INTO tblDay VALUES (1319,1249948800,11,8,2009,2);
INSERT INTO tblDay VALUES (1320,1250035200,12,8,2009,3);
INSERT INTO tblDay VALUES (1321,1250121600,13,8,2009,4);
INSERT INTO tblDay VALUES (1322,1250208000,14,8,2009,5);
INSERT INTO tblDay VALUES (1323,1250294400,15,8,2009,6);
INSERT INTO tblDay VALUES (1324,1250380800,16,8,2009,7);
INSERT INTO tblDay VALUES (1325,1250467200,17,8,2009,1);
INSERT INTO tblDay VALUES (1326,1250553600,18,8,2009,2);
INSERT INTO tblDay VALUES (1327,1250640000,19,8,2009,3);
INSERT INTO tblDay VALUES (1328,1250726400,20,8,2009,4);
INSERT INTO tblDay VALUES (1329,1250812800,21,8,2009,5);
INSERT INTO tblDay VALUES (1330,1250899200,22,8,2009,6);
INSERT INTO tblDay VALUES (1331,1250985600,23,8,2009,7);
INSERT INTO tblDay VALUES (1332,1251072000,24,8,2009,1);
INSERT INTO tblDay VALUES (1333,1251158400,25,8,2009,2);
INSERT INTO tblDay VALUES (1334,1251244800,26,8,2009,3);
INSERT INTO tblDay VALUES (1335,1251331200,27,8,2009,4);
INSERT INTO tblDay VALUES (1336,1251417600,28,8,2009,5);
INSERT INTO tblDay VALUES (1337,1251504000,29,8,2009,6);
INSERT INTO tblDay VALUES (1338,1251590400,30,8,2009,7);
INSERT INTO tblDay VALUES (1339,1251676800,31,8,2009,1);
INSERT INTO tblDay VALUES (1340,1251763200,1,9,2009,2);
INSERT INTO tblDay VALUES (1341,1251849600,2,9,2009,3);
INSERT INTO tblDay VALUES (1342,1251936000,3,9,2009,4);
INSERT INTO tblDay VALUES (1343,1252022400,4,9,2009,5);
INSERT INTO tblDay VALUES (1344,1252108800,5,9,2009,6);
INSERT INTO tblDay VALUES (1345,1252195200,6,9,2009,7);
INSERT INTO tblDay VALUES (1346,1252281600,7,9,2009,1);
INSERT INTO tblDay VALUES (1347,1252368000,8,9,2009,2);
INSERT INTO tblDay VALUES (1348,1252454400,9,9,2009,3);
INSERT INTO tblDay VALUES (1349,1252540800,10,9,2009,4);
INSERT INTO tblDay VALUES (1350,1252627200,11,9,2009,5);
INSERT INTO tblDay VALUES (1351,1252713600,12,9,2009,6);
INSERT INTO tblDay VALUES (1352,1252800000,13,9,2009,7);
INSERT INTO tblDay VALUES (1353,1252886400,14,9,2009,1);
INSERT INTO tblDay VALUES (1354,1252972800,15,9,2009,2);
INSERT INTO tblDay VALUES (1355,1253059200,16,9,2009,3);
INSERT INTO tblDay VALUES (1356,1253145600,17,9,2009,4);
INSERT INTO tblDay VALUES (1357,1253232000,18,9,2009,5);
INSERT INTO tblDay VALUES (1358,1253318400,19,9,2009,6);
INSERT INTO tblDay VALUES (1359,1253404800,20,9,2009,7);
INSERT INTO tblDay VALUES (1360,1253491200,21,9,2009,1);
INSERT INTO tblDay VALUES (1361,1253577600,22,9,2009,2);
INSERT INTO tblDay VALUES (1362,1253664000,23,9,2009,3);
INSERT INTO tblDay VALUES (1363,1253750400,24,9,2009,4);
INSERT INTO tblDay VALUES (1364,1253836800,25,9,2009,5);
INSERT INTO tblDay VALUES (1365,1253923200,26,9,2009,6);
INSERT INTO tblDay VALUES (1366,1254009600,27,9,2009,7);
INSERT INTO tblDay VALUES (1367,1254096000,28,9,2009,1);
INSERT INTO tblDay VALUES (1368,1254182400,29,9,2009,2);
INSERT INTO tblDay VALUES (1369,1254268800,30,9,2009,3);
INSERT INTO tblDay VALUES (1370,1254355200,1,10,2009,4);
INSERT INTO tblDay VALUES (1371,1254441600,2,10,2009,5);
INSERT INTO tblDay VALUES (1372,1254528000,3,10,2009,6);
INSERT INTO tblDay VALUES (1373,1254614400,4,10,2009,7);
INSERT INTO tblDay VALUES (1374,1254700800,5,10,2009,1);
INSERT INTO tblDay VALUES (1375,1254787200,6,10,2009,2);
INSERT INTO tblDay VALUES (1376,1254873600,7,10,2009,3);
INSERT INTO tblDay VALUES (1377,1254960000,8,10,2009,4);
INSERT INTO tblDay VALUES (1378,1255046400,9,10,2009,5);
INSERT INTO tblDay VALUES (1379,1255132800,10,10,2009,6);
INSERT INTO tblDay VALUES (1380,1255219200,11,10,2009,7);
INSERT INTO tblDay VALUES (1381,1255305600,12,10,2009,1);
INSERT INTO tblDay VALUES (1382,1255392000,13,10,2009,2);
INSERT INTO tblDay VALUES (1383,1255478400,14,10,2009,3);
INSERT INTO tblDay VALUES (1384,1255564800,15,10,2009,4);
INSERT INTO tblDay VALUES (1385,1255651200,16,10,2009,5);
INSERT INTO tblDay VALUES (1386,1255737600,17,10,2009,6);
INSERT INTO tblDay VALUES (1387,1255824000,18,10,2009,7);
INSERT INTO tblDay VALUES (1388,1255910400,19,10,2009,1);
INSERT INTO tblDay VALUES (1389,1255996800,20,10,2009,2);
INSERT INTO tblDay VALUES (1390,1256083200,21,10,2009,3);
INSERT INTO tblDay VALUES (1391,1256169600,22,10,2009,4);
INSERT INTO tblDay VALUES (1392,1256256000,23,10,2009,5);
INSERT INTO tblDay VALUES (1393,1256342400,24,10,2009,6);
INSERT INTO tblDay VALUES (1394,1256428800,25,10,2009,7);
INSERT INTO tblDay VALUES (1395,1256515200,26,10,2009,1);
INSERT INTO tblDay VALUES (1396,1256601600,27,10,2009,2);
INSERT INTO tblDay VALUES (1397,1256688000,28,10,2009,3);
INSERT INTO tblDay VALUES (1398,1256774400,29,10,2009,4);
INSERT INTO tblDay VALUES (1399,1256860800,30,10,2009,5);
INSERT INTO tblDay VALUES (1400,1256947200,31,10,2009,6);
INSERT INTO tblDay VALUES (1401,1257033600,1,11,2009,7);
INSERT INTO tblDay VALUES (1402,1257120000,2,11,2009,1);
INSERT INTO tblDay VALUES (1403,1257206400,3,11,2009,2);
INSERT INTO tblDay VALUES (1404,1257292800,4,11,2009,3);
INSERT INTO tblDay VALUES (1405,1257379200,5,11,2009,4);
INSERT INTO tblDay VALUES (1406,1257465600,6,11,2009,5);
INSERT INTO tblDay VALUES (1407,1257552000,7,11,2009,6);
INSERT INTO tblDay VALUES (1408,1257638400,8,11,2009,7);
INSERT INTO tblDay VALUES (1409,1257724800,9,11,2009,1);
INSERT INTO tblDay VALUES (1410,1257811200,10,11,2009,2);
INSERT INTO tblDay VALUES (1411,1257897600,11,11,2009,3);
INSERT INTO tblDay VALUES (1412,1257984000,12,11,2009,4);
INSERT INTO tblDay VALUES (1413,1258070400,13,11,2009,5);
INSERT INTO tblDay VALUES (1414,1258156800,14,11,2009,6);
INSERT INTO tblDay VALUES (1415,1258243200,15,11,2009,7);
INSERT INTO tblDay VALUES (1416,1258329600,16,11,2009,1);
INSERT INTO tblDay VALUES (1417,1258416000,17,11,2009,2);
INSERT INTO tblDay VALUES (1418,1258502400,18,11,2009,3);
INSERT INTO tblDay VALUES (1419,1258588800,19,11,2009,4);
INSERT INTO tblDay VALUES (1420,1258675200,20,11,2009,5);
INSERT INTO tblDay VALUES (1421,1258761600,21,11,2009,6);
INSERT INTO tblDay VALUES (1422,1258848000,22,11,2009,7);
INSERT INTO tblDay VALUES (1423,1258934400,23,11,2009,1);
INSERT INTO tblDay VALUES (1424,1259020800,24,11,2009,2);
INSERT INTO tblDay VALUES (1425,1259107200,25,11,2009,3);
INSERT INTO tblDay VALUES (1426,1259193600,26,11,2009,4);
INSERT INTO tblDay VALUES (1427,1259280000,27,11,2009,5);
INSERT INTO tblDay VALUES (1428,1259366400,28,11,2009,6);
INSERT INTO tblDay VALUES (1429,1259452800,29,11,2009,7);
INSERT INTO tblDay VALUES (1430,1259539200,30,11,2009,1);
INSERT INTO tblDay VALUES (1431,1259625600,1,12,2009,2);
INSERT INTO tblDay VALUES (1432,1259712000,2,12,2009,3);
INSERT INTO tblDay VALUES (1433,1259798400,3,12,2009,4);
INSERT INTO tblDay VALUES (1434,1259884800,4,12,2009,5);
INSERT INTO tblDay VALUES (1435,1259971200,5,12,2009,6);
INSERT INTO tblDay VALUES (1436,1260057600,6,12,2009,7);
INSERT INTO tblDay VALUES (1437,1260144000,7,12,2009,1);
INSERT INTO tblDay VALUES (1438,1260230400,8,12,2009,2);
INSERT INTO tblDay VALUES (1439,1260316800,9,12,2009,3);
INSERT INTO tblDay VALUES (1440,1260403200,10,12,2009,4);
INSERT INTO tblDay VALUES (1441,1260489600,11,12,2009,5);
INSERT INTO tblDay VALUES (1442,1260576000,12,12,2009,6);
INSERT INTO tblDay VALUES (1443,1260662400,13,12,2009,7);
INSERT INTO tblDay VALUES (1444,1260748800,14,12,2009,1);
INSERT INTO tblDay VALUES (1445,1260835200,15,12,2009,2);
INSERT INTO tblDay VALUES (1446,1260921600,16,12,2009,3);
INSERT INTO tblDay VALUES (1447,1261008000,17,12,2009,4);
INSERT INTO tblDay VALUES (1448,1261094400,18,12,2009,5);
INSERT INTO tblDay VALUES (1449,1261180800,19,12,2009,6);
INSERT INTO tblDay VALUES (1450,1261267200,20,12,2009,7);
INSERT INTO tblDay VALUES (1451,1261353600,21,12,2009,1);
INSERT INTO tblDay VALUES (1452,1261440000,22,12,2009,2);
INSERT INTO tblDay VALUES (1453,1261526400,23,12,2009,3);
INSERT INTO tblDay VALUES (1454,1261612800,24,12,2009,4);
INSERT INTO tblDay VALUES (1455,1261699200,25,12,2009,5);
INSERT INTO tblDay VALUES (1456,1261785600,26,12,2009,6);
INSERT INTO tblDay VALUES (1457,1261872000,27,12,2009,7);
INSERT INTO tblDay VALUES (1458,1261958400,28,12,2009,1);
INSERT INTO tblDay VALUES (1459,1262044800,29,12,2009,2);
INSERT INTO tblDay VALUES (1460,1262131200,30,12,2009,3);
INSERT INTO tblDay VALUES (1461,1262217600,31,12,2009,4);
INSERT INTO tblDay VALUES (1462,1262304000,1,1,2010,5);
INSERT INTO tblDay VALUES (1463,1262390400,2,1,2010,6);
INSERT INTO tblDay VALUES (1464,1262476800,3,1,2010,7);
INSERT INTO tblDay VALUES (1465,1262563200,4,1,2010,1);
INSERT INTO tblDay VALUES (1466,1262649600,5,1,2010,2);
INSERT INTO tblDay VALUES (1467,1262736000,6,1,2010,3);
INSERT INTO tblDay VALUES (1468,1262822400,7,1,2010,4);
INSERT INTO tblDay VALUES (1469,1262908800,8,1,2010,5);
INSERT INTO tblDay VALUES (1470,1262995200,9,1,2010,6);
INSERT INTO tblDay VALUES (1471,1263081600,10,1,2010,7);
INSERT INTO tblDay VALUES (1472,1263168000,11,1,2010,1);
INSERT INTO tblDay VALUES (1473,1263254400,12,1,2010,2);
INSERT INTO tblDay VALUES (1474,1263340800,13,1,2010,3);
INSERT INTO tblDay VALUES (1475,1263427200,14,1,2010,4);
INSERT INTO tblDay VALUES (1476,1263513600,15,1,2010,5);
INSERT INTO tblDay VALUES (1477,1263600000,16,1,2010,6);
INSERT INTO tblDay VALUES (1478,1263686400,17,1,2010,7);
INSERT INTO tblDay VALUES (1479,1263772800,18,1,2010,1);
INSERT INTO tblDay VALUES (1480,1263859200,19,1,2010,2);
INSERT INTO tblDay VALUES (1481,1263945600,20,1,2010,3);
INSERT INTO tblDay VALUES (1482,1264032000,21,1,2010,4);
INSERT INTO tblDay VALUES (1483,1264118400,22,1,2010,5);
INSERT INTO tblDay VALUES (1484,1264204800,23,1,2010,6);
INSERT INTO tblDay VALUES (1485,1264291200,24,1,2010,7);
INSERT INTO tblDay VALUES (1486,1264377600,25,1,2010,1);
INSERT INTO tblDay VALUES (1487,1264464000,26,1,2010,2);
INSERT INTO tblDay VALUES (1488,1264550400,27,1,2010,3);
INSERT INTO tblDay VALUES (1489,1264636800,28,1,2010,4);
INSERT INTO tblDay VALUES (1490,1264723200,29,1,2010,5);
INSERT INTO tblDay VALUES (1491,1264809600,30,1,2010,6);
INSERT INTO tblDay VALUES (1492,1264896000,31,1,2010,7);
INSERT INTO tblDay VALUES (1493,1264982400,1,2,2010,1);
INSERT INTO tblDay VALUES (1494,1265068800,2,2,2010,2);
INSERT INTO tblDay VALUES (1495,1265155200,3,2,2010,3);
INSERT INTO tblDay VALUES (1496,1265241600,4,2,2010,4);
INSERT INTO tblDay VALUES (1497,1265328000,5,2,2010,5);
INSERT INTO tblDay VALUES (1498,1265414400,6,2,2010,6);
INSERT INTO tblDay VALUES (1499,1265500800,7,2,2010,7);
INSERT INTO tblDay VALUES (1500,1265587200,8,2,2010,1);
INSERT INTO tblDay VALUES (1501,1265673600,9,2,2010,2);
INSERT INTO tblDay VALUES (1502,1265760000,10,2,2010,3);
INSERT INTO tblDay VALUES (1503,1265846400,11,2,2010,4);
INSERT INTO tblDay VALUES (1504,1265932800,12,2,2010,5);
INSERT INTO tblDay VALUES (1505,1266019200,13,2,2010,6);
INSERT INTO tblDay VALUES (1506,1266105600,14,2,2010,7);
INSERT INTO tblDay VALUES (1507,1266192000,15,2,2010,1);
INSERT INTO tblDay VALUES (1508,1266278400,16,2,2010,2);
INSERT INTO tblDay VALUES (1509,1266364800,17,2,2010,3);
INSERT INTO tblDay VALUES (1510,1266451200,18,2,2010,4);
INSERT INTO tblDay VALUES (1511,1266537600,19,2,2010,5);
INSERT INTO tblDay VALUES (1512,1266624000,20,2,2010,6);
INSERT INTO tblDay VALUES (1513,1266710400,21,2,2010,7);
INSERT INTO tblDay VALUES (1514,1266796800,22,2,2010,1);
INSERT INTO tblDay VALUES (1515,1266883200,23,2,2010,2);
INSERT INTO tblDay VALUES (1516,1266969600,24,2,2010,3);
INSERT INTO tblDay VALUES (1517,1267056000,25,2,2010,4);
INSERT INTO tblDay VALUES (1518,1267142400,26,2,2010,5);
INSERT INTO tblDay VALUES (1519,1267228800,27,2,2010,6);
INSERT INTO tblDay VALUES (1520,1267315200,28,2,2010,7);
INSERT INTO tblDay VALUES (1521,1267401600,1,3,2010,1);
INSERT INTO tblDay VALUES (1522,1267488000,2,3,2010,2);
INSERT INTO tblDay VALUES (1523,1267574400,3,3,2010,3);
INSERT INTO tblDay VALUES (1524,1267660800,4,3,2010,4);
INSERT INTO tblDay VALUES (1525,1267747200,5,3,2010,5);
INSERT INTO tblDay VALUES (1526,1267833600,6,3,2010,6);
INSERT INTO tblDay VALUES (1527,1267920000,7,3,2010,7);
INSERT INTO tblDay VALUES (1528,1268006400,8,3,2010,1);
INSERT INTO tblDay VALUES (1529,1268092800,9,3,2010,2);
INSERT INTO tblDay VALUES (1530,1268179200,10,3,2010,3);
INSERT INTO tblDay VALUES (1531,1268265600,11,3,2010,4);
INSERT INTO tblDay VALUES (1532,1268352000,12,3,2010,5);
INSERT INTO tblDay VALUES (1533,1268438400,13,3,2010,6);
INSERT INTO tblDay VALUES (1534,1268524800,14,3,2010,7);
INSERT INTO tblDay VALUES (1535,1268611200,15,3,2010,1);
INSERT INTO tblDay VALUES (1536,1268697600,16,3,2010,2);
INSERT INTO tblDay VALUES (1537,1268784000,17,3,2010,3);
INSERT INTO tblDay VALUES (1538,1268870400,18,3,2010,4);
INSERT INTO tblDay VALUES (1539,1268956800,19,3,2010,5);
INSERT INTO tblDay VALUES (1540,1269043200,20,3,2010,6);
INSERT INTO tblDay VALUES (1541,1269129600,21,3,2010,7);
INSERT INTO tblDay VALUES (1542,1269216000,22,3,2010,1);
INSERT INTO tblDay VALUES (1543,1269302400,23,3,2010,2);
INSERT INTO tblDay VALUES (1544,1269388800,24,3,2010,3);
INSERT INTO tblDay VALUES (1545,1269475200,25,3,2010,4);
INSERT INTO tblDay VALUES (1546,1269561600,26,3,2010,5);
INSERT INTO tblDay VALUES (1547,1269648000,27,3,2010,6);
INSERT INTO tblDay VALUES (1548,1269734400,28,3,2010,7);
INSERT INTO tblDay VALUES (1549,1269820800,29,3,2010,1);
INSERT INTO tblDay VALUES (1550,1269907200,30,3,2010,2);
INSERT INTO tblDay VALUES (1551,1269993600,31,3,2010,3);
INSERT INTO tblDay VALUES (1552,1270080000,1,4,2010,4);
INSERT INTO tblDay VALUES (1553,1270166400,2,4,2010,5);
INSERT INTO tblDay VALUES (1554,1270252800,3,4,2010,6);
INSERT INTO tblDay VALUES (1555,1270339200,4,4,2010,7);
INSERT INTO tblDay VALUES (1556,1270425600,5,4,2010,1);
INSERT INTO tblDay VALUES (1557,1270512000,6,4,2010,2);
INSERT INTO tblDay VALUES (1558,1270598400,7,4,2010,3);
INSERT INTO tblDay VALUES (1559,1270684800,8,4,2010,4);
INSERT INTO tblDay VALUES (1560,1270771200,9,4,2010,5);
INSERT INTO tblDay VALUES (1561,1270857600,10,4,2010,6);
INSERT INTO tblDay VALUES (1562,1270944000,11,4,2010,7);
INSERT INTO tblDay VALUES (1563,1271030400,12,4,2010,1);
INSERT INTO tblDay VALUES (1564,1271116800,13,4,2010,2);
INSERT INTO tblDay VALUES (1565,1271203200,14,4,2010,3);
INSERT INTO tblDay VALUES (1566,1271289600,15,4,2010,4);
INSERT INTO tblDay VALUES (1567,1271376000,16,4,2010,5);
INSERT INTO tblDay VALUES (1568,1271462400,17,4,2010,6);
INSERT INTO tblDay VALUES (1569,1271548800,18,4,2010,7);
INSERT INTO tblDay VALUES (1570,1271635200,19,4,2010,1);
INSERT INTO tblDay VALUES (1571,1271721600,20,4,2010,2);
INSERT INTO tblDay VALUES (1572,1271808000,21,4,2010,3);
INSERT INTO tblDay VALUES (1573,1271894400,22,4,2010,4);
INSERT INTO tblDay VALUES (1574,1271980800,23,4,2010,5);
INSERT INTO tblDay VALUES (1575,1272067200,24,4,2010,6);
INSERT INTO tblDay VALUES (1576,1272153600,25,4,2010,7);
INSERT INTO tblDay VALUES (1577,1272240000,26,4,2010,1);
INSERT INTO tblDay VALUES (1578,1272326400,27,4,2010,2);
INSERT INTO tblDay VALUES (1579,1272412800,28,4,2010,3);
INSERT INTO tblDay VALUES (1580,1272499200,29,4,2010,4);
INSERT INTO tblDay VALUES (1581,1272585600,30,4,2010,5);
INSERT INTO tblDay VALUES (1582,1272672000,1,5,2010,6);
INSERT INTO tblDay VALUES (1583,1272758400,2,5,2010,7);
INSERT INTO tblDay VALUES (1584,1272844800,3,5,2010,1);
INSERT INTO tblDay VALUES (1585,1272931200,4,5,2010,2);
INSERT INTO tblDay VALUES (1586,1273017600,5,5,2010,3);
INSERT INTO tblDay VALUES (1587,1273104000,6,5,2010,4);
INSERT INTO tblDay VALUES (1588,1273190400,7,5,2010,5);
INSERT INTO tblDay VALUES (1589,1273276800,8,5,2010,6);
INSERT INTO tblDay VALUES (1590,1273363200,9,5,2010,7);
INSERT INTO tblDay VALUES (1591,1273449600,10,5,2010,1);
INSERT INTO tblDay VALUES (1592,1273536000,11,5,2010,2);
INSERT INTO tblDay VALUES (1593,1273622400,12,5,2010,3);
INSERT INTO tblDay VALUES (1594,1273708800,13,5,2010,4);
INSERT INTO tblDay VALUES (1595,1273795200,14,5,2010,5);
INSERT INTO tblDay VALUES (1596,1273881600,15,5,2010,6);
INSERT INTO tblDay VALUES (1597,1273968000,16,5,2010,7);
INSERT INTO tblDay VALUES (1598,1274054400,17,5,2010,1);
INSERT INTO tblDay VALUES (1599,1274140800,18,5,2010,2);
INSERT INTO tblDay VALUES (1600,1274227200,19,5,2010,3);
INSERT INTO tblDay VALUES (1601,1274313600,20,5,2010,4);
INSERT INTO tblDay VALUES (1602,1274400000,21,5,2010,5);
INSERT INTO tblDay VALUES (1603,1274486400,22,5,2010,6);
INSERT INTO tblDay VALUES (1604,1274572800,23,5,2010,7);
INSERT INTO tblDay VALUES (1605,1274659200,24,5,2010,1);
INSERT INTO tblDay VALUES (1606,1274745600,25,5,2010,2);
INSERT INTO tblDay VALUES (1607,1274832000,26,5,2010,3);
INSERT INTO tblDay VALUES (1608,1274918400,27,5,2010,4);
INSERT INTO tblDay VALUES (1609,1275004800,28,5,2010,5);
INSERT INTO tblDay VALUES (1610,1275091200,29,5,2010,6);
INSERT INTO tblDay VALUES (1611,1275177600,30,5,2010,7);
INSERT INTO tblDay VALUES (1612,1275264000,31,5,2010,1);
INSERT INTO tblDay VALUES (1613,1275350400,1,6,2010,2);
INSERT INTO tblDay VALUES (1614,1275436800,2,6,2010,3);
INSERT INTO tblDay VALUES (1615,1275523200,3,6,2010,4);
INSERT INTO tblDay VALUES (1616,1275609600,4,6,2010,5);
INSERT INTO tblDay VALUES (1617,1275696000,5,6,2010,6);
INSERT INTO tblDay VALUES (1618,1275782400,6,6,2010,7);
INSERT INTO tblDay VALUES (1619,1275868800,7,6,2010,1);
INSERT INTO tblDay VALUES (1620,1275955200,8,6,2010,2);
INSERT INTO tblDay VALUES (1621,1276041600,9,6,2010,3);
INSERT INTO tblDay VALUES (1622,1276128000,10,6,2010,4);
INSERT INTO tblDay VALUES (1623,1276214400,11,6,2010,5);
INSERT INTO tblDay VALUES (1624,1276300800,12,6,2010,6);
INSERT INTO tblDay VALUES (1625,1276387200,13,6,2010,7);
INSERT INTO tblDay VALUES (1626,1276473600,14,6,2010,1);
INSERT INTO tblDay VALUES (1627,1276560000,15,6,2010,2);
INSERT INTO tblDay VALUES (1628,1276646400,16,6,2010,3);
INSERT INTO tblDay VALUES (1629,1276732800,17,6,2010,4);
INSERT INTO tblDay VALUES (1630,1276819200,18,6,2010,5);
INSERT INTO tblDay VALUES (1631,1276905600,19,6,2010,6);
INSERT INTO tblDay VALUES (1632,1276992000,20,6,2010,7);
INSERT INTO tblDay VALUES (1633,1277078400,21,6,2010,1);
INSERT INTO tblDay VALUES (1634,1277164800,22,6,2010,2);
INSERT INTO tblDay VALUES (1635,1277251200,23,6,2010,3);
INSERT INTO tblDay VALUES (1636,1277337600,24,6,2010,4);
INSERT INTO tblDay VALUES (1637,1277424000,25,6,2010,5);
INSERT INTO tblDay VALUES (1638,1277510400,26,6,2010,6);
INSERT INTO tblDay VALUES (1639,1277596800,27,6,2010,7);
INSERT INTO tblDay VALUES (1640,1277683200,28,6,2010,1);
INSERT INTO tblDay VALUES (1641,1277769600,29,6,2010,2);
INSERT INTO tblDay VALUES (1642,1277856000,30,6,2010,3);
INSERT INTO tblDay VALUES (1643,1277942400,1,7,2010,4);
INSERT INTO tblDay VALUES (1644,1278028800,2,7,2010,5);
INSERT INTO tblDay VALUES (1645,1278115200,3,7,2010,6);
INSERT INTO tblDay VALUES (1646,1278201600,4,7,2010,7);
INSERT INTO tblDay VALUES (1647,1278288000,5,7,2010,1);
INSERT INTO tblDay VALUES (1648,1278374400,6,7,2010,2);
INSERT INTO tblDay VALUES (1649,1278460800,7,7,2010,3);
INSERT INTO tblDay VALUES (1650,1278547200,8,7,2010,4);
INSERT INTO tblDay VALUES (1651,1278633600,9,7,2010,5);
INSERT INTO tblDay VALUES (1652,1278720000,10,7,2010,6);
INSERT INTO tblDay VALUES (1653,1278806400,11,7,2010,7);
INSERT INTO tblDay VALUES (1654,1278892800,12,7,2010,1);
INSERT INTO tblDay VALUES (1655,1278979200,13,7,2010,2);
INSERT INTO tblDay VALUES (1656,1279065600,14,7,2010,3);
INSERT INTO tblDay VALUES (1657,1279152000,15,7,2010,4);
INSERT INTO tblDay VALUES (1658,1279238400,16,7,2010,5);
INSERT INTO tblDay VALUES (1659,1279324800,17,7,2010,6);
INSERT INTO tblDay VALUES (1660,1279411200,18,7,2010,7);
INSERT INTO tblDay VALUES (1661,1279497600,19,7,2010,1);
INSERT INTO tblDay VALUES (1662,1279584000,20,7,2010,2);
INSERT INTO tblDay VALUES (1663,1279670400,21,7,2010,3);
INSERT INTO tblDay VALUES (1664,1279756800,22,7,2010,4);
INSERT INTO tblDay VALUES (1665,1279843200,23,7,2010,5);
INSERT INTO tblDay VALUES (1666,1279929600,24,7,2010,6);
INSERT INTO tblDay VALUES (1667,1280016000,25,7,2010,7);
INSERT INTO tblDay VALUES (1668,1280102400,26,7,2010,1);
INSERT INTO tblDay VALUES (1669,1280188800,27,7,2010,2);
INSERT INTO tblDay VALUES (1670,1280275200,28,7,2010,3);
INSERT INTO tblDay VALUES (1671,1280361600,29,7,2010,4);
INSERT INTO tblDay VALUES (1672,1280448000,30,7,2010,5);
INSERT INTO tblDay VALUES (1673,1280534400,31,7,2010,6);
INSERT INTO tblDay VALUES (1674,1280620800,1,8,2010,7);
INSERT INTO tblDay VALUES (1675,1280707200,2,8,2010,1);
INSERT INTO tblDay VALUES (1676,1280793600,3,8,2010,2);
INSERT INTO tblDay VALUES (1677,1280880000,4,8,2010,3);
INSERT INTO tblDay VALUES (1678,1280966400,5,8,2010,4);
INSERT INTO tblDay VALUES (1679,1281052800,6,8,2010,5);
INSERT INTO tblDay VALUES (1680,1281139200,7,8,2010,6);
INSERT INTO tblDay VALUES (1681,1281225600,8,8,2010,7);
INSERT INTO tblDay VALUES (1682,1281312000,9,8,2010,1);
INSERT INTO tblDay VALUES (1683,1281398400,10,8,2010,2);
INSERT INTO tblDay VALUES (1684,1281484800,11,8,2010,3);
INSERT INTO tblDay VALUES (1685,1281571200,12,8,2010,4);
INSERT INTO tblDay VALUES (1686,1281657600,13,8,2010,5);
INSERT INTO tblDay VALUES (1687,1281744000,14,8,2010,6);
INSERT INTO tblDay VALUES (1688,1281830400,15,8,2010,7);
INSERT INTO tblDay VALUES (1689,1281916800,16,8,2010,1);
INSERT INTO tblDay VALUES (1690,1282003200,17,8,2010,2);
INSERT INTO tblDay VALUES (1691,1282089600,18,8,2010,3);
INSERT INTO tblDay VALUES (1692,1282176000,19,8,2010,4);
INSERT INTO tblDay VALUES (1693,1282262400,20,8,2010,5);
INSERT INTO tblDay VALUES (1694,1282348800,21,8,2010,6);
INSERT INTO tblDay VALUES (1695,1282435200,22,8,2010,7);
INSERT INTO tblDay VALUES (1696,1282521600,23,8,2010,1);
INSERT INTO tblDay VALUES (1697,1282608000,24,8,2010,2);
INSERT INTO tblDay VALUES (1698,1282694400,25,8,2010,3);
INSERT INTO tblDay VALUES (1699,1282780800,26,8,2010,4);
INSERT INTO tblDay VALUES (1700,1282867200,27,8,2010,5);
INSERT INTO tblDay VALUES (1701,1282953600,28,8,2010,6);
INSERT INTO tblDay VALUES (1702,1283040000,29,8,2010,7);
INSERT INTO tblDay VALUES (1703,1283126400,30,8,2010,1);
INSERT INTO tblDay VALUES (1704,1283212800,31,8,2010,2);
INSERT INTO tblDay VALUES (1705,1283299200,1,9,2010,3);
INSERT INTO tblDay VALUES (1706,1283385600,2,9,2010,4);
INSERT INTO tblDay VALUES (1707,1283472000,3,9,2010,5);
INSERT INTO tblDay VALUES (1708,1283558400,4,9,2010,6);
INSERT INTO tblDay VALUES (1709,1283644800,5,9,2010,7);
INSERT INTO tblDay VALUES (1710,1283731200,6,9,2010,1);
INSERT INTO tblDay VALUES (1711,1283817600,7,9,2010,2);
INSERT INTO tblDay VALUES (1712,1283904000,8,9,2010,3);
INSERT INTO tblDay VALUES (1713,1283990400,9,9,2010,4);
INSERT INTO tblDay VALUES (1714,1284076800,10,9,2010,5);
INSERT INTO tblDay VALUES (1715,1284163200,11,9,2010,6);
INSERT INTO tblDay VALUES (1716,1284249600,12,9,2010,7);
INSERT INTO tblDay VALUES (1717,1284336000,13,9,2010,1);
INSERT INTO tblDay VALUES (1718,1284422400,14,9,2010,2);
INSERT INTO tblDay VALUES (1719,1284508800,15,9,2010,3);
INSERT INTO tblDay VALUES (1720,1284595200,16,9,2010,4);
INSERT INTO tblDay VALUES (1721,1284681600,17,9,2010,5);
INSERT INTO tblDay VALUES (1722,1284768000,18,9,2010,6);
INSERT INTO tblDay VALUES (1723,1284854400,19,9,2010,7);
INSERT INTO tblDay VALUES (1724,1284940800,20,9,2010,1);
INSERT INTO tblDay VALUES (1725,1285027200,21,9,2010,2);
INSERT INTO tblDay VALUES (1726,1285113600,22,9,2010,3);
INSERT INTO tblDay VALUES (1727,1285200000,23,9,2010,4);
INSERT INTO tblDay VALUES (1728,1285286400,24,9,2010,5);
INSERT INTO tblDay VALUES (1729,1285372800,25,9,2010,6);
INSERT INTO tblDay VALUES (1730,1285459200,26,9,2010,7);
INSERT INTO tblDay VALUES (1731,1285545600,27,9,2010,1);
INSERT INTO tblDay VALUES (1732,1285632000,28,9,2010,2);
INSERT INTO tblDay VALUES (1733,1285718400,29,9,2010,3);
INSERT INTO tblDay VALUES (1734,1285804800,30,9,2010,4);
INSERT INTO tblDay VALUES (1735,1285891200,1,10,2010,5);
INSERT INTO tblDay VALUES (1736,1285977600,2,10,2010,6);
INSERT INTO tblDay VALUES (1737,1286064000,3,10,2010,7);
INSERT INTO tblDay VALUES (1738,1286150400,4,10,2010,1);
INSERT INTO tblDay VALUES (1739,1286236800,5,10,2010,2);
INSERT INTO tblDay VALUES (1740,1286323200,6,10,2010,3);
INSERT INTO tblDay VALUES (1741,1286409600,7,10,2010,4);
INSERT INTO tblDay VALUES (1742,1286496000,8,10,2010,5);
INSERT INTO tblDay VALUES (1743,1286582400,9,10,2010,6);
INSERT INTO tblDay VALUES (1744,1286668800,10,10,2010,7);
INSERT INTO tblDay VALUES (1745,1286755200,11,10,2010,1);
INSERT INTO tblDay VALUES (1746,1286841600,12,10,2010,2);
INSERT INTO tblDay VALUES (1747,1286928000,13,10,2010,3);
INSERT INTO tblDay VALUES (1748,1287014400,14,10,2010,4);
INSERT INTO tblDay VALUES (1749,1287100800,15,10,2010,5);
INSERT INTO tblDay VALUES (1750,1287187200,16,10,2010,6);
INSERT INTO tblDay VALUES (1751,1287273600,17,10,2010,7);
INSERT INTO tblDay VALUES (1752,1287360000,18,10,2010,1);
INSERT INTO tblDay VALUES (1753,1287446400,19,10,2010,2);
INSERT INTO tblDay VALUES (1754,1287532800,20,10,2010,3);
INSERT INTO tblDay VALUES (1755,1287619200,21,10,2010,4);
INSERT INTO tblDay VALUES (1756,1287705600,22,10,2010,5);
INSERT INTO tblDay VALUES (1757,1287792000,23,10,2010,6);
INSERT INTO tblDay VALUES (1758,1287878400,24,10,2010,7);
INSERT INTO tblDay VALUES (1759,1287964800,25,10,2010,1);
INSERT INTO tblDay VALUES (1760,1288051200,26,10,2010,2);
INSERT INTO tblDay VALUES (1761,1288137600,27,10,2010,3);
INSERT INTO tblDay VALUES (1762,1288224000,28,10,2010,4);
INSERT INTO tblDay VALUES (1763,1288310400,29,10,2010,5);
INSERT INTO tblDay VALUES (1764,1288396800,30,10,2010,6);
INSERT INTO tblDay VALUES (1765,1288483200,31,10,2010,7);
INSERT INTO tblDay VALUES (1766,1288569600,1,11,2010,1);
INSERT INTO tblDay VALUES (1767,1288656000,2,11,2010,2);
INSERT INTO tblDay VALUES (1768,1288742400,3,11,2010,3);
INSERT INTO tblDay VALUES (1769,1288828800,4,11,2010,4);
INSERT INTO tblDay VALUES (1770,1288915200,5,11,2010,5);
INSERT INTO tblDay VALUES (1771,1289001600,6,11,2010,6);
INSERT INTO tblDay VALUES (1772,1289088000,7,11,2010,7);
INSERT INTO tblDay VALUES (1773,1289174400,8,11,2010,1);
INSERT INTO tblDay VALUES (1774,1289260800,9,11,2010,2);
INSERT INTO tblDay VALUES (1775,1289347200,10,11,2010,3);
INSERT INTO tblDay VALUES (1776,1289433600,11,11,2010,4);
INSERT INTO tblDay VALUES (1777,1289520000,12,11,2010,5);
INSERT INTO tblDay VALUES (1778,1289606400,13,11,2010,6);
INSERT INTO tblDay VALUES (1779,1289692800,14,11,2010,7);
INSERT INTO tblDay VALUES (1780,1289779200,15,11,2010,1);
INSERT INTO tblDay VALUES (1781,1289865600,16,11,2010,2);
INSERT INTO tblDay VALUES (1782,1289952000,17,11,2010,3);
INSERT INTO tblDay VALUES (1783,1290038400,18,11,2010,4);
INSERT INTO tblDay VALUES (1784,1290124800,19,11,2010,5);
INSERT INTO tblDay VALUES (1785,1290211200,20,11,2010,6);
INSERT INTO tblDay VALUES (1786,1290297600,21,11,2010,7);
INSERT INTO tblDay VALUES (1787,1290384000,22,11,2010,1);
INSERT INTO tblDay VALUES (1788,1290470400,23,11,2010,2);
INSERT INTO tblDay VALUES (1789,1290556800,24,11,2010,3);
INSERT INTO tblDay VALUES (1790,1290643200,25,11,2010,4);
INSERT INTO tblDay VALUES (1791,1290729600,26,11,2010,5);
INSERT INTO tblDay VALUES (1792,1290816000,27,11,2010,6);
INSERT INTO tblDay VALUES (1793,1290902400,28,11,2010,7);
INSERT INTO tblDay VALUES (1794,1290988800,29,11,2010,1);
INSERT INTO tblDay VALUES (1795,1291075200,30,11,2010,2);
INSERT INTO tblDay VALUES (1796,1291161600,1,12,2010,3);
INSERT INTO tblDay VALUES (1797,1291248000,2,12,2010,4);
INSERT INTO tblDay VALUES (1798,1291334400,3,12,2010,5);
INSERT INTO tblDay VALUES (1799,1291420800,4,12,2010,6);
INSERT INTO tblDay VALUES (1800,1291507200,5,12,2010,7);
INSERT INTO tblDay VALUES (1801,1291593600,6,12,2010,1);
INSERT INTO tblDay VALUES (1802,1291680000,7,12,2010,2);
INSERT INTO tblDay VALUES (1803,1291766400,8,12,2010,3);
INSERT INTO tblDay VALUES (1804,1291852800,9,12,2010,4);
INSERT INTO tblDay VALUES (1805,1291939200,10,12,2010,5);
INSERT INTO tblDay VALUES (1806,1292025600,11,12,2010,6);
INSERT INTO tblDay VALUES (1807,1292112000,12,12,2010,7);
INSERT INTO tblDay VALUES (1808,1292198400,13,12,2010,1);
INSERT INTO tblDay VALUES (1809,1292284800,14,12,2010,2);
INSERT INTO tblDay VALUES (1810,1292371200,15,12,2010,3);
INSERT INTO tblDay VALUES (1811,1292457600,16,12,2010,4);
INSERT INTO tblDay VALUES (1812,1292544000,17,12,2010,5);
INSERT INTO tblDay VALUES (1813,1292630400,18,12,2010,6);
INSERT INTO tblDay VALUES (1814,1292716800,19,12,2010,7);
INSERT INTO tblDay VALUES (1815,1292803200,20,12,2010,1);
INSERT INTO tblDay VALUES (1816,1292889600,21,12,2010,2);
INSERT INTO tblDay VALUES (1817,1292976000,22,12,2010,3);
INSERT INTO tblDay VALUES (1818,1293062400,23,12,2010,4);
INSERT INTO tblDay VALUES (1819,1293148800,24,12,2010,5);
INSERT INTO tblDay VALUES (1820,1293235200,25,12,2010,6);
INSERT INTO tblDay VALUES (1821,1293321600,26,12,2010,7);
INSERT INTO tblDay VALUES (1822,1293408000,27,12,2010,1);
INSERT INTO tblDay VALUES (1823,1293494400,28,12,2010,2);
INSERT INTO tblDay VALUES (1824,1293580800,29,12,2010,3);
INSERT INTO tblDay VALUES (1825,1293667200,30,12,2010,4);
INSERT INTO tblDay VALUES (1826,1293753600,31,12,2010,5);
INSERT INTO tblDay VALUES (1827,1293840000,1,1,2011,6);
INSERT INTO tblDay VALUES (1828,1293926400,2,1,2011,7);
INSERT INTO tblDay VALUES (1829,1294012800,3,1,2011,1);
INSERT INTO tblDay VALUES (1830,1294099200,4,1,2011,2);
INSERT INTO tblDay VALUES (1831,1294185600,5,1,2011,3);
INSERT INTO tblDay VALUES (1832,1294272000,6,1,2011,4);
INSERT INTO tblDay VALUES (1833,1294358400,7,1,2011,5);
INSERT INTO tblDay VALUES (1834,1294444800,8,1,2011,6);
INSERT INTO tblDay VALUES (1835,1294531200,9,1,2011,7);
INSERT INTO tblDay VALUES (1836,1294617600,10,1,2011,1);
INSERT INTO tblDay VALUES (1837,1294704000,11,1,2011,2);
INSERT INTO tblDay VALUES (1838,1294790400,12,1,2011,3);
INSERT INTO tblDay VALUES (1839,1294876800,13,1,2011,4);
INSERT INTO tblDay VALUES (1840,1294963200,14,1,2011,5);
INSERT INTO tblDay VALUES (1841,1295049600,15,1,2011,6);
INSERT INTO tblDay VALUES (1842,1295136000,16,1,2011,7);
INSERT INTO tblDay VALUES (1843,1295222400,17,1,2011,1);
INSERT INTO tblDay VALUES (1844,1295308800,18,1,2011,2);
INSERT INTO tblDay VALUES (1845,1295395200,19,1,2011,3);
INSERT INTO tblDay VALUES (1846,1295481600,20,1,2011,4);
INSERT INTO tblDay VALUES (1847,1295568000,21,1,2011,5);
INSERT INTO tblDay VALUES (1848,1295654400,22,1,2011,6);
INSERT INTO tblDay VALUES (1849,1295740800,23,1,2011,7);
INSERT INTO tblDay VALUES (1850,1295827200,24,1,2011,1);
INSERT INTO tblDay VALUES (1851,1295913600,25,1,2011,2);
INSERT INTO tblDay VALUES (1852,1296000000,26,1,2011,3);
INSERT INTO tblDay VALUES (1853,1296086400,27,1,2011,4);
INSERT INTO tblDay VALUES (1854,1296172800,28,1,2011,5);
INSERT INTO tblDay VALUES (1855,1296259200,29,1,2011,6);
INSERT INTO tblDay VALUES (1856,1296345600,30,1,2011,7);
INSERT INTO tblDay VALUES (1857,1296432000,31,1,2011,1);
INSERT INTO tblDay VALUES (1858,1296518400,1,2,2011,2);
INSERT INTO tblDay VALUES (1859,1296604800,2,2,2011,3);
INSERT INTO tblDay VALUES (1860,1296691200,3,2,2011,4);
INSERT INTO tblDay VALUES (1861,1296777600,4,2,2011,5);
INSERT INTO tblDay VALUES (1862,1296864000,5,2,2011,6);
INSERT INTO tblDay VALUES (1863,1296950400,6,2,2011,7);
INSERT INTO tblDay VALUES (1864,1297036800,7,2,2011,1);
INSERT INTO tblDay VALUES (1865,1297123200,8,2,2011,2);
INSERT INTO tblDay VALUES (1866,1297209600,9,2,2011,3);
INSERT INTO tblDay VALUES (1867,1297296000,10,2,2011,4);
INSERT INTO tblDay VALUES (1868,1297382400,11,2,2011,5);
INSERT INTO tblDay VALUES (1869,1297468800,12,2,2011,6);
INSERT INTO tblDay VALUES (1870,1297555200,13,2,2011,7);
INSERT INTO tblDay VALUES (1871,1297641600,14,2,2011,1);
INSERT INTO tblDay VALUES (1872,1297728000,15,2,2011,2);
INSERT INTO tblDay VALUES (1873,1297814400,16,2,2011,3);
INSERT INTO tblDay VALUES (1874,1297900800,17,2,2011,4);
INSERT INTO tblDay VALUES (1875,1297987200,18,2,2011,5);
INSERT INTO tblDay VALUES (1876,1298073600,19,2,2011,6);
INSERT INTO tblDay VALUES (1877,1298160000,20,2,2011,7);
INSERT INTO tblDay VALUES (1878,1298246400,21,2,2011,1);
INSERT INTO tblDay VALUES (1879,1298332800,22,2,2011,2);
INSERT INTO tblDay VALUES (1880,1298419200,23,2,2011,3);
INSERT INTO tblDay VALUES (1881,1298505600,24,2,2011,4);
INSERT INTO tblDay VALUES (1882,1298592000,25,2,2011,5);
INSERT INTO tblDay VALUES (1883,1298678400,26,2,2011,6);
INSERT INTO tblDay VALUES (1884,1298764800,27,2,2011,7);
INSERT INTO tblDay VALUES (1885,1298851200,28,2,2011,1);
INSERT INTO tblDay VALUES (1886,1298937600,1,3,2011,2);
INSERT INTO tblDay VALUES (1887,1299024000,2,3,2011,3);
INSERT INTO tblDay VALUES (1888,1299110400,3,3,2011,4);
INSERT INTO tblDay VALUES (1889,1299196800,4,3,2011,5);
INSERT INTO tblDay VALUES (1890,1299283200,5,3,2011,6);
INSERT INTO tblDay VALUES (1891,1299369600,6,3,2011,7);
INSERT INTO tblDay VALUES (1892,1299456000,7,3,2011,1);
INSERT INTO tblDay VALUES (1893,1299542400,8,3,2011,2);
INSERT INTO tblDay VALUES (1894,1299628800,9,3,2011,3);
INSERT INTO tblDay VALUES (1895,1299715200,10,3,2011,4);
INSERT INTO tblDay VALUES (1896,1299801600,11,3,2011,5);
INSERT INTO tblDay VALUES (1897,1299888000,12,3,2011,6);
INSERT INTO tblDay VALUES (1898,1299974400,13,3,2011,7);
INSERT INTO tblDay VALUES (1899,1300060800,14,3,2011,1);
INSERT INTO tblDay VALUES (1900,1300147200,15,3,2011,2);
INSERT INTO tblDay VALUES (1901,1300233600,16,3,2011,3);
INSERT INTO tblDay VALUES (1902,1300320000,17,3,2011,4);
INSERT INTO tblDay VALUES (1903,1300406400,18,3,2011,5);
INSERT INTO tblDay VALUES (1904,1300492800,19,3,2011,6);
INSERT INTO tblDay VALUES (1905,1300579200,20,3,2011,7);
INSERT INTO tblDay VALUES (1906,1300665600,21,3,2011,1);
INSERT INTO tblDay VALUES (1907,1300752000,22,3,2011,2);
INSERT INTO tblDay VALUES (1908,1300838400,23,3,2011,3);
INSERT INTO tblDay VALUES (1909,1300924800,24,3,2011,4);
INSERT INTO tblDay VALUES (1910,1301011200,25,3,2011,5);
INSERT INTO tblDay VALUES (1911,1301097600,26,3,2011,6);
INSERT INTO tblDay VALUES (1912,1301184000,27,3,2011,7);
INSERT INTO tblDay VALUES (1913,1301270400,28,3,2011,1);
INSERT INTO tblDay VALUES (1914,1301356800,29,3,2011,2);
INSERT INTO tblDay VALUES (1915,1301443200,30,3,2011,3);
INSERT INTO tblDay VALUES (1916,1301529600,31,3,2011,4);
INSERT INTO tblDay VALUES (1917,1301616000,1,4,2011,5);
INSERT INTO tblDay VALUES (1918,1301702400,2,4,2011,6);
INSERT INTO tblDay VALUES (1919,1301788800,3,4,2011,7);
INSERT INTO tblDay VALUES (1920,1301875200,4,4,2011,1);
INSERT INTO tblDay VALUES (1921,1301961600,5,4,2011,2);
INSERT INTO tblDay VALUES (1922,1302048000,6,4,2011,3);
INSERT INTO tblDay VALUES (1923,1302134400,7,4,2011,4);
INSERT INTO tblDay VALUES (1924,1302220800,8,4,2011,5);
INSERT INTO tblDay VALUES (1925,1302307200,9,4,2011,6);
INSERT INTO tblDay VALUES (1926,1302393600,10,4,2011,7);
INSERT INTO tblDay VALUES (1927,1302480000,11,4,2011,1);
INSERT INTO tblDay VALUES (1928,1302566400,12,4,2011,2);
INSERT INTO tblDay VALUES (1929,1302652800,13,4,2011,3);
INSERT INTO tblDay VALUES (1930,1302739200,14,4,2011,4);
INSERT INTO tblDay VALUES (1931,1302825600,15,4,2011,5);
INSERT INTO tblDay VALUES (1932,1302912000,16,4,2011,6);
INSERT INTO tblDay VALUES (1933,1302998400,17,4,2011,7);
INSERT INTO tblDay VALUES (1934,1303084800,18,4,2011,1);
INSERT INTO tblDay VALUES (1935,1303171200,19,4,2011,2);
INSERT INTO tblDay VALUES (1936,1303257600,20,4,2011,3);
INSERT INTO tblDay VALUES (1937,1303344000,21,4,2011,4);
INSERT INTO tblDay VALUES (1938,1303430400,22,4,2011,5);
INSERT INTO tblDay VALUES (1939,1303516800,23,4,2011,6);
INSERT INTO tblDay VALUES (1940,1303603200,24,4,2011,7);
INSERT INTO tblDay VALUES (1941,1303689600,25,4,2011,1);
INSERT INTO tblDay VALUES (1942,1303776000,26,4,2011,2);
INSERT INTO tblDay VALUES (1943,1303862400,27,4,2011,3);
INSERT INTO tblDay VALUES (1944,1303948800,28,4,2011,4);
INSERT INTO tblDay VALUES (1945,1304035200,29,4,2011,5);
INSERT INTO tblDay VALUES (1946,1304121600,30,4,2011,6);
INSERT INTO tblDay VALUES (1947,1304208000,1,5,2011,7);
INSERT INTO tblDay VALUES (1948,1304294400,2,5,2011,1);
INSERT INTO tblDay VALUES (1949,1304380800,3,5,2011,2);
INSERT INTO tblDay VALUES (1950,1304467200,4,5,2011,3);
INSERT INTO tblDay VALUES (1951,1304553600,5,5,2011,4);
INSERT INTO tblDay VALUES (1952,1304640000,6,5,2011,5);
INSERT INTO tblDay VALUES (1953,1304726400,7,5,2011,6);
INSERT INTO tblDay VALUES (1954,1304812800,8,5,2011,7);
INSERT INTO tblDay VALUES (1955,1304899200,9,5,2011,1);
INSERT INTO tblDay VALUES (1956,1304985600,10,5,2011,2);
INSERT INTO tblDay VALUES (1957,1305072000,11,5,2011,3);
INSERT INTO tblDay VALUES (1958,1305158400,12,5,2011,4);
INSERT INTO tblDay VALUES (1959,1305244800,13,5,2011,5);
INSERT INTO tblDay VALUES (1960,1305331200,14,5,2011,6);
INSERT INTO tblDay VALUES (1961,1305417600,15,5,2011,7);
INSERT INTO tblDay VALUES (1962,1305504000,16,5,2011,1);
INSERT INTO tblDay VALUES (1963,1305590400,17,5,2011,2);
INSERT INTO tblDay VALUES (1964,1305676800,18,5,2011,3);
INSERT INTO tblDay VALUES (1965,1305763200,19,5,2011,4);
INSERT INTO tblDay VALUES (1966,1305849600,20,5,2011,5);
INSERT INTO tblDay VALUES (1967,1305936000,21,5,2011,6);
INSERT INTO tblDay VALUES (1968,1306022400,22,5,2011,7);
INSERT INTO tblDay VALUES (1969,1306108800,23,5,2011,1);
INSERT INTO tblDay VALUES (1970,1306195200,24,5,2011,2);
INSERT INTO tblDay VALUES (1971,1306281600,25,5,2011,3);
INSERT INTO tblDay VALUES (1972,1306368000,26,5,2011,4);
INSERT INTO tblDay VALUES (1973,1306454400,27,5,2011,5);
INSERT INTO tblDay VALUES (1974,1306540800,28,5,2011,6);
INSERT INTO tblDay VALUES (1975,1306627200,29,5,2011,7);
INSERT INTO tblDay VALUES (1976,1306713600,30,5,2011,1);
INSERT INTO tblDay VALUES (1977,1306800000,31,5,2011,2);
INSERT INTO tblDay VALUES (1978,1306886400,1,6,2011,3);
INSERT INTO tblDay VALUES (1979,1306972800,2,6,2011,4);
INSERT INTO tblDay VALUES (1980,1307059200,3,6,2011,5);
INSERT INTO tblDay VALUES (1981,1307145600,4,6,2011,6);
INSERT INTO tblDay VALUES (1982,1307232000,5,6,2011,7);
INSERT INTO tblDay VALUES (1983,1307318400,6,6,2011,1);
INSERT INTO tblDay VALUES (1984,1307404800,7,6,2011,2);
INSERT INTO tblDay VALUES (1985,1307491200,8,6,2011,3);
INSERT INTO tblDay VALUES (1986,1307577600,9,6,2011,4);
INSERT INTO tblDay VALUES (1987,1307664000,10,6,2011,5);
INSERT INTO tblDay VALUES (1988,1307750400,11,6,2011,6);
INSERT INTO tblDay VALUES (1989,1307836800,12,6,2011,7);
INSERT INTO tblDay VALUES (1990,1307923200,13,6,2011,1);
INSERT INTO tblDay VALUES (1991,1308009600,14,6,2011,2);
INSERT INTO tblDay VALUES (1992,1308096000,15,6,2011,3);
INSERT INTO tblDay VALUES (1993,1308182400,16,6,2011,4);
INSERT INTO tblDay VALUES (1994,1308268800,17,6,2011,5);
INSERT INTO tblDay VALUES (1995,1308355200,18,6,2011,6);
INSERT INTO tblDay VALUES (1996,1308441600,19,6,2011,7);
INSERT INTO tblDay VALUES (1997,1308528000,20,6,2011,1);
INSERT INTO tblDay VALUES (1998,1308614400,21,6,2011,2);
INSERT INTO tblDay VALUES (1999,1308700800,22,6,2011,3);
INSERT INTO tblDay VALUES (2000,1308787200,23,6,2011,4);
INSERT INTO tblDay VALUES (2001,1308873600,24,6,2011,5);
INSERT INTO tblDay VALUES (2002,1308960000,25,6,2011,6);
INSERT INTO tblDay VALUES (2003,1309046400,26,6,2011,7);
INSERT INTO tblDay VALUES (2004,1309132800,27,6,2011,1);
INSERT INTO tblDay VALUES (2005,1309219200,28,6,2011,2);
INSERT INTO tblDay VALUES (2006,1309305600,29,6,2011,3);
INSERT INTO tblDay VALUES (2007,1309392000,30,6,2011,4);
INSERT INTO tblDay VALUES (2008,1309478400,1,7,2011,5);
INSERT INTO tblDay VALUES (2009,1309564800,2,7,2011,6);
INSERT INTO tblDay VALUES (2010,1309651200,3,7,2011,7);
INSERT INTO tblDay VALUES (2011,1309737600,4,7,2011,1);
INSERT INTO tblDay VALUES (2012,1309824000,5,7,2011,2);
INSERT INTO tblDay VALUES (2013,1309910400,6,7,2011,3);
INSERT INTO tblDay VALUES (2014,1309996800,7,7,2011,4);
INSERT INTO tblDay VALUES (2015,1310083200,8,7,2011,5);
INSERT INTO tblDay VALUES (2016,1310169600,9,7,2011,6);
INSERT INTO tblDay VALUES (2017,1310256000,10,7,2011,7);
INSERT INTO tblDay VALUES (2018,1310342400,11,7,2011,1);
INSERT INTO tblDay VALUES (2019,1310428800,12,7,2011,2);
INSERT INTO tblDay VALUES (2020,1310515200,13,7,2011,3);
INSERT INTO tblDay VALUES (2021,1310601600,14,7,2011,4);
INSERT INTO tblDay VALUES (2022,1310688000,15,7,2011,5);
INSERT INTO tblDay VALUES (2023,1310774400,16,7,2011,6);
INSERT INTO tblDay VALUES (2024,1310860800,17,7,2011,7);
INSERT INTO tblDay VALUES (2025,1310947200,18,7,2011,1);
INSERT INTO tblDay VALUES (2026,1311033600,19,7,2011,2);
INSERT INTO tblDay VALUES (2027,1311120000,20,7,2011,3);
INSERT INTO tblDay VALUES (2028,1311206400,21,7,2011,4);
INSERT INTO tblDay VALUES (2029,1311292800,22,7,2011,5);
INSERT INTO tblDay VALUES (2030,1311379200,23,7,2011,6);
INSERT INTO tblDay VALUES (2031,1311465600,24,7,2011,7);
INSERT INTO tblDay VALUES (2032,1311552000,25,7,2011,1);
INSERT INTO tblDay VALUES (2033,1311638400,26,7,2011,2);
INSERT INTO tblDay VALUES (2034,1311724800,27,7,2011,3);
INSERT INTO tblDay VALUES (2035,1311811200,28,7,2011,4);
INSERT INTO tblDay VALUES (2036,1311897600,29,7,2011,5);
INSERT INTO tblDay VALUES (2037,1311984000,30,7,2011,6);
INSERT INTO tblDay VALUES (2038,1312070400,31,7,2011,7);
INSERT INTO tblDay VALUES (2039,1312156800,1,8,2011,1);
INSERT INTO tblDay VALUES (2040,1312243200,2,8,2011,2);
INSERT INTO tblDay VALUES (2041,1312329600,3,8,2011,3);
INSERT INTO tblDay VALUES (2042,1312416000,4,8,2011,4);
INSERT INTO tblDay VALUES (2043,1312502400,5,8,2011,5);
INSERT INTO tblDay VALUES (2044,1312588800,6,8,2011,6);
INSERT INTO tblDay VALUES (2045,1312675200,7,8,2011,7);
INSERT INTO tblDay VALUES (2046,1312761600,8,8,2011,1);
INSERT INTO tblDay VALUES (2047,1312848000,9,8,2011,2);
INSERT INTO tblDay VALUES (2048,1312934400,10,8,2011,3);
INSERT INTO tblDay VALUES (2049,1313020800,11,8,2011,4);
INSERT INTO tblDay VALUES (2050,1313107200,12,8,2011,5);
INSERT INTO tblDay VALUES (2051,1313193600,13,8,2011,6);
INSERT INTO tblDay VALUES (2052,1313280000,14,8,2011,7);
INSERT INTO tblDay VALUES (2053,1313366400,15,8,2011,1);
INSERT INTO tblDay VALUES (2054,1313452800,16,8,2011,2);
INSERT INTO tblDay VALUES (2055,1313539200,17,8,2011,3);
INSERT INTO tblDay VALUES (2056,1313625600,18,8,2011,4);
INSERT INTO tblDay VALUES (2057,1313712000,19,8,2011,5);
INSERT INTO tblDay VALUES (2058,1313798400,20,8,2011,6);
INSERT INTO tblDay VALUES (2059,1313884800,21,8,2011,7);
INSERT INTO tblDay VALUES (2060,1313971200,22,8,2011,1);
INSERT INTO tblDay VALUES (2061,1314057600,23,8,2011,2);
INSERT INTO tblDay VALUES (2062,1314144000,24,8,2011,3);
INSERT INTO tblDay VALUES (2063,1314230400,25,8,2011,4);
INSERT INTO tblDay VALUES (2064,1314316800,26,8,2011,5);
INSERT INTO tblDay VALUES (2065,1314403200,27,8,2011,6);
INSERT INTO tblDay VALUES (2066,1314489600,28,8,2011,7);
INSERT INTO tblDay VALUES (2067,1314576000,29,8,2011,1);
INSERT INTO tblDay VALUES (2068,1314662400,30,8,2011,2);
INSERT INTO tblDay VALUES (2069,1314748800,31,8,2011,3);
INSERT INTO tblDay VALUES (2070,1314835200,1,9,2011,4);
INSERT INTO tblDay VALUES (2071,1314921600,2,9,2011,5);
INSERT INTO tblDay VALUES (2072,1315008000,3,9,2011,6);
INSERT INTO tblDay VALUES (2073,1315094400,4,9,2011,7);
INSERT INTO tblDay VALUES (2074,1315180800,5,9,2011,1);
INSERT INTO tblDay VALUES (2075,1315267200,6,9,2011,2);
INSERT INTO tblDay VALUES (2076,1315353600,7,9,2011,3);
INSERT INTO tblDay VALUES (2077,1315440000,8,9,2011,4);
INSERT INTO tblDay VALUES (2078,1315526400,9,9,2011,5);
INSERT INTO tblDay VALUES (2079,1315612800,10,9,2011,6);
INSERT INTO tblDay VALUES (2080,1315699200,11,9,2011,7);
INSERT INTO tblDay VALUES (2081,1315785600,12,9,2011,1);
INSERT INTO tblDay VALUES (2082,1315872000,13,9,2011,2);
INSERT INTO tblDay VALUES (2083,1315958400,14,9,2011,3);
INSERT INTO tblDay VALUES (2084,1316044800,15,9,2011,4);
INSERT INTO tblDay VALUES (2085,1316131200,16,9,2011,5);
INSERT INTO tblDay VALUES (2086,1316217600,17,9,2011,6);
INSERT INTO tblDay VALUES (2087,1316304000,18,9,2011,7);
INSERT INTO tblDay VALUES (2088,1316390400,19,9,2011,1);
INSERT INTO tblDay VALUES (2089,1316476800,20,9,2011,2);
INSERT INTO tblDay VALUES (2090,1316563200,21,9,2011,3);
INSERT INTO tblDay VALUES (2091,1316649600,22,9,2011,4);
INSERT INTO tblDay VALUES (2092,1316736000,23,9,2011,5);
INSERT INTO tblDay VALUES (2093,1316822400,24,9,2011,6);
INSERT INTO tblDay VALUES (2094,1316908800,25,9,2011,7);
INSERT INTO tblDay VALUES (2095,1316995200,26,9,2011,1);
INSERT INTO tblDay VALUES (2096,1317081600,27,9,2011,2);
INSERT INTO tblDay VALUES (2097,1317168000,28,9,2011,3);
INSERT INTO tblDay VALUES (2098,1317254400,29,9,2011,4);
INSERT INTO tblDay VALUES (2099,1317340800,30,9,2011,5);
INSERT INTO tblDay VALUES (2100,1317427200,1,10,2011,6);
INSERT INTO tblDay VALUES (2101,1317513600,2,10,2011,7);
INSERT INTO tblDay VALUES (2102,1317600000,3,10,2011,1);
INSERT INTO tblDay VALUES (2103,1317686400,4,10,2011,2);
INSERT INTO tblDay VALUES (2104,1317772800,5,10,2011,3);
INSERT INTO tblDay VALUES (2105,1317859200,6,10,2011,4);
INSERT INTO tblDay VALUES (2106,1317945600,7,10,2011,5);
INSERT INTO tblDay VALUES (2107,1318032000,8,10,2011,6);
INSERT INTO tblDay VALUES (2108,1318118400,9,10,2011,7);
INSERT INTO tblDay VALUES (2109,1318204800,10,10,2011,1);
INSERT INTO tblDay VALUES (2110,1318291200,11,10,2011,2);
INSERT INTO tblDay VALUES (2111,1318377600,12,10,2011,3);
INSERT INTO tblDay VALUES (2112,1318464000,13,10,2011,4);
INSERT INTO tblDay VALUES (2113,1318550400,14,10,2011,5);
INSERT INTO tblDay VALUES (2114,1318636800,15,10,2011,6);
INSERT INTO tblDay VALUES (2115,1318723200,16,10,2011,7);
INSERT INTO tblDay VALUES (2116,1318809600,17,10,2011,1);
INSERT INTO tblDay VALUES (2117,1318896000,18,10,2011,2);
INSERT INTO tblDay VALUES (2118,1318982400,19,10,2011,3);
INSERT INTO tblDay VALUES (2119,1319068800,20,10,2011,4);
INSERT INTO tblDay VALUES (2120,1319155200,21,10,2011,5);
INSERT INTO tblDay VALUES (2121,1319241600,22,10,2011,6);
INSERT INTO tblDay VALUES (2122,1319328000,23,10,2011,7);
INSERT INTO tblDay VALUES (2123,1319414400,24,10,2011,1);
INSERT INTO tblDay VALUES (2124,1319500800,25,10,2011,2);
INSERT INTO tblDay VALUES (2125,1319587200,26,10,2011,3);
INSERT INTO tblDay VALUES (2126,1319673600,27,10,2011,4);
INSERT INTO tblDay VALUES (2127,1319760000,28,10,2011,5);
INSERT INTO tblDay VALUES (2128,1319846400,29,10,2011,6);
INSERT INTO tblDay VALUES (2129,1319932800,30,10,2011,7);
INSERT INTO tblDay VALUES (2130,1320019200,31,10,2011,1);
INSERT INTO tblDay VALUES (2131,1320105600,1,11,2011,2);
INSERT INTO tblDay VALUES (2132,1320192000,2,11,2011,3);
INSERT INTO tblDay VALUES (2133,1320278400,3,11,2011,4);
INSERT INTO tblDay VALUES (2134,1320364800,4,11,2011,5);
INSERT INTO tblDay VALUES (2135,1320451200,5,11,2011,6);
INSERT INTO tblDay VALUES (2136,1320537600,6,11,2011,7);
INSERT INTO tblDay VALUES (2137,1320624000,7,11,2011,1);
INSERT INTO tblDay VALUES (2138,1320710400,8,11,2011,2);
INSERT INTO tblDay VALUES (2139,1320796800,9,11,2011,3);
INSERT INTO tblDay VALUES (2140,1320883200,10,11,2011,4);
INSERT INTO tblDay VALUES (2141,1320969600,11,11,2011,5);
INSERT INTO tblDay VALUES (2142,1321056000,12,11,2011,6);
INSERT INTO tblDay VALUES (2143,1321142400,13,11,2011,7);
INSERT INTO tblDay VALUES (2144,1321228800,14,11,2011,1);
INSERT INTO tblDay VALUES (2145,1321315200,15,11,2011,2);
INSERT INTO tblDay VALUES (2146,1321401600,16,11,2011,3);
INSERT INTO tblDay VALUES (2147,1321488000,17,11,2011,4);
INSERT INTO tblDay VALUES (2148,1321574400,18,11,2011,5);
INSERT INTO tblDay VALUES (2149,1321660800,19,11,2011,6);
INSERT INTO tblDay VALUES (2150,1321747200,20,11,2011,7);
INSERT INTO tblDay VALUES (2151,1321833600,21,11,2011,1);
INSERT INTO tblDay VALUES (2152,1321920000,22,11,2011,2);
INSERT INTO tblDay VALUES (2153,1322006400,23,11,2011,3);
INSERT INTO tblDay VALUES (2154,1322092800,24,11,2011,4);
INSERT INTO tblDay VALUES (2155,1322179200,25,11,2011,5);
INSERT INTO tblDay VALUES (2156,1322265600,26,11,2011,6);
INSERT INTO tblDay VALUES (2157,1322352000,27,11,2011,7);
INSERT INTO tblDay VALUES (2158,1322438400,28,11,2011,1);
INSERT INTO tblDay VALUES (2159,1322524800,29,11,2011,2);
INSERT INTO tblDay VALUES (2160,1322611200,30,11,2011,3);
INSERT INTO tblDay VALUES (2161,1322697600,1,12,2011,4);
INSERT INTO tblDay VALUES (2162,1322784000,2,12,2011,5);
INSERT INTO tblDay VALUES (2163,1322870400,3,12,2011,6);
INSERT INTO tblDay VALUES (2164,1322956800,4,12,2011,7);
INSERT INTO tblDay VALUES (2165,1323043200,5,12,2011,1);
INSERT INTO tblDay VALUES (2166,1323129600,6,12,2011,2);
INSERT INTO tblDay VALUES (2167,1323216000,7,12,2011,3);
INSERT INTO tblDay VALUES (2168,1323302400,8,12,2011,4);
INSERT INTO tblDay VALUES (2169,1323388800,9,12,2011,5);
INSERT INTO tblDay VALUES (2170,1323475200,10,12,2011,6);
INSERT INTO tblDay VALUES (2171,1323561600,11,12,2011,7);
INSERT INTO tblDay VALUES (2172,1323648000,12,12,2011,1);
INSERT INTO tblDay VALUES (2173,1323734400,13,12,2011,2);
INSERT INTO tblDay VALUES (2174,1323820800,14,12,2011,3);
INSERT INTO tblDay VALUES (2175,1323907200,15,12,2011,4);
INSERT INTO tblDay VALUES (2176,1323993600,16,12,2011,5);
INSERT INTO tblDay VALUES (2177,1324080000,17,12,2011,6);
INSERT INTO tblDay VALUES (2178,1324166400,18,12,2011,7);
INSERT INTO tblDay VALUES (2179,1324252800,19,12,2011,1);
INSERT INTO tblDay VALUES (2180,1324339200,20,12,2011,2);
INSERT INTO tblDay VALUES (2181,1324425600,21,12,2011,3);
INSERT INTO tblDay VALUES (2182,1324512000,22,12,2011,4);
INSERT INTO tblDay VALUES (2183,1324598400,23,12,2011,5);
INSERT INTO tblDay VALUES (2184,1324684800,24,12,2011,6);
INSERT INTO tblDay VALUES (2185,1324771200,25,12,2011,7);
INSERT INTO tblDay VALUES (2186,1324857600,26,12,2011,1);
INSERT INTO tblDay VALUES (2187,1324944000,27,12,2011,2);
INSERT INTO tblDay VALUES (2188,1325030400,28,12,2011,3);
INSERT INTO tblDay VALUES (2189,1325116800,29,12,2011,4);
INSERT INTO tblDay VALUES (2190,1325203200,30,12,2011,5);
INSERT INTO tblDay VALUES (2191,1325289600,31,12,2011,6);
INSERT INTO tblDay VALUES (2192,1325376000,1,1,2012,7);
INSERT INTO tblDay VALUES (2193,1325462400,2,1,2012,1);
INSERT INTO tblDay VALUES (2194,1325548800,3,1,2012,2);
INSERT INTO tblDay VALUES (2195,1325635200,4,1,2012,3);
INSERT INTO tblDay VALUES (2196,1325721600,5,1,2012,4);
INSERT INTO tblDay VALUES (2197,1325808000,6,1,2012,5);
INSERT INTO tblDay VALUES (2198,1325894400,7,1,2012,6);
INSERT INTO tblDay VALUES (2199,1325980800,8,1,2012,7);
INSERT INTO tblDay VALUES (2200,1326067200,9,1,2012,1);
INSERT INTO tblDay VALUES (2201,1326153600,10,1,2012,2);
INSERT INTO tblDay VALUES (2202,1326240000,11,1,2012,3);
INSERT INTO tblDay VALUES (2203,1326326400,12,1,2012,4);
INSERT INTO tblDay VALUES (2204,1326412800,13,1,2012,5);
INSERT INTO tblDay VALUES (2205,1326499200,14,1,2012,6);
INSERT INTO tblDay VALUES (2206,1326585600,15,1,2012,7);
INSERT INTO tblDay VALUES (2207,1326672000,16,1,2012,1);
INSERT INTO tblDay VALUES (2208,1326758400,17,1,2012,2);
INSERT INTO tblDay VALUES (2209,1326844800,18,1,2012,3);
INSERT INTO tblDay VALUES (2210,1326931200,19,1,2012,4);
INSERT INTO tblDay VALUES (2211,1327017600,20,1,2012,5);
INSERT INTO tblDay VALUES (2212,1327104000,21,1,2012,6);
INSERT INTO tblDay VALUES (2213,1327190400,22,1,2012,7);
INSERT INTO tblDay VALUES (2214,1327276800,23,1,2012,1);
INSERT INTO tblDay VALUES (2215,1327363200,24,1,2012,2);
INSERT INTO tblDay VALUES (2216,1327449600,25,1,2012,3);
INSERT INTO tblDay VALUES (2217,1327536000,26,1,2012,4);
INSERT INTO tblDay VALUES (2218,1327622400,27,1,2012,5);
INSERT INTO tblDay VALUES (2219,1327708800,28,1,2012,6);
INSERT INTO tblDay VALUES (2220,1327795200,29,1,2012,7);
INSERT INTO tblDay VALUES (2221,1327881600,30,1,2012,1);
INSERT INTO tblDay VALUES (2222,1327968000,31,1,2012,2);
INSERT INTO tblDay VALUES (2223,1328054400,1,2,2012,3);
INSERT INTO tblDay VALUES (2224,1328140800,2,2,2012,4);
INSERT INTO tblDay VALUES (2225,1328227200,3,2,2012,5);
INSERT INTO tblDay VALUES (2226,1328313600,4,2,2012,6);
INSERT INTO tblDay VALUES (2227,1328400000,5,2,2012,7);
INSERT INTO tblDay VALUES (2228,1328486400,6,2,2012,1);
INSERT INTO tblDay VALUES (2229,1328572800,7,2,2012,2);
INSERT INTO tblDay VALUES (2230,1328659200,8,2,2012,3);
INSERT INTO tblDay VALUES (2231,1328745600,9,2,2012,4);
INSERT INTO tblDay VALUES (2232,1328832000,10,2,2012,5);
INSERT INTO tblDay VALUES (2233,1328918400,11,2,2012,6);
INSERT INTO tblDay VALUES (2234,1329004800,12,2,2012,7);
INSERT INTO tblDay VALUES (2235,1329091200,13,2,2012,1);
INSERT INTO tblDay VALUES (2236,1329177600,14,2,2012,2);
INSERT INTO tblDay VALUES (2237,1329264000,15,2,2012,3);
INSERT INTO tblDay VALUES (2238,1329350400,16,2,2012,4);
INSERT INTO tblDay VALUES (2239,1329436800,17,2,2012,5);
INSERT INTO tblDay VALUES (2240,1329523200,18,2,2012,6);
INSERT INTO tblDay VALUES (2241,1329609600,19,2,2012,7);
INSERT INTO tblDay VALUES (2242,1329696000,20,2,2012,1);
INSERT INTO tblDay VALUES (2243,1329782400,21,2,2012,2);
INSERT INTO tblDay VALUES (2244,1329868800,22,2,2012,3);
INSERT INTO tblDay VALUES (2245,1329955200,23,2,2012,4);
INSERT INTO tblDay VALUES (2246,1330041600,24,2,2012,5);
INSERT INTO tblDay VALUES (2247,1330128000,25,2,2012,6);
INSERT INTO tblDay VALUES (2248,1330214400,26,2,2012,7);
INSERT INTO tblDay VALUES (2249,1330300800,27,2,2012,1);
INSERT INTO tblDay VALUES (2250,1330387200,28,2,2012,2);
INSERT INTO tblDay VALUES (2251,1330473600,29,2,2012,3);
INSERT INTO tblDay VALUES (2252,1330560000,1,3,2012,4);
INSERT INTO tblDay VALUES (2253,1330646400,2,3,2012,5);
INSERT INTO tblDay VALUES (2254,1330732800,3,3,2012,6);
INSERT INTO tblDay VALUES (2255,1330819200,4,3,2012,7);
INSERT INTO tblDay VALUES (2256,1330905600,5,3,2012,1);
INSERT INTO tblDay VALUES (2257,1330992000,6,3,2012,2);
INSERT INTO tblDay VALUES (2258,1331078400,7,3,2012,3);
INSERT INTO tblDay VALUES (2259,1331164800,8,3,2012,4);
INSERT INTO tblDay VALUES (2260,1331251200,9,3,2012,5);
INSERT INTO tblDay VALUES (2261,1331337600,10,3,2012,6);
INSERT INTO tblDay VALUES (2262,1331424000,11,3,2012,7);
INSERT INTO tblDay VALUES (2263,1331510400,12,3,2012,1);
INSERT INTO tblDay VALUES (2264,1331596800,13,3,2012,2);
INSERT INTO tblDay VALUES (2265,1331683200,14,3,2012,3);
INSERT INTO tblDay VALUES (2266,1331769600,15,3,2012,4);
INSERT INTO tblDay VALUES (2267,1331856000,16,3,2012,5);
INSERT INTO tblDay VALUES (2268,1331942400,17,3,2012,6);
INSERT INTO tblDay VALUES (2269,1332028800,18,3,2012,7);
INSERT INTO tblDay VALUES (2270,1332115200,19,3,2012,1);
INSERT INTO tblDay VALUES (2271,1332201600,20,3,2012,2);
INSERT INTO tblDay VALUES (2272,1332288000,21,3,2012,3);
INSERT INTO tblDay VALUES (2273,1332374400,22,3,2012,4);
INSERT INTO tblDay VALUES (2274,1332460800,23,3,2012,5);
INSERT INTO tblDay VALUES (2275,1332547200,24,3,2012,6);
INSERT INTO tblDay VALUES (2276,1332633600,25,3,2012,7);
INSERT INTO tblDay VALUES (2277,1332720000,26,3,2012,1);
INSERT INTO tblDay VALUES (2278,1332806400,27,3,2012,2);
INSERT INTO tblDay VALUES (2279,1332892800,28,3,2012,3);
INSERT INTO tblDay VALUES (2280,1332979200,29,3,2012,4);
INSERT INTO tblDay VALUES (2281,1333065600,30,3,2012,5);
INSERT INTO tblDay VALUES (2282,1333152000,31,3,2012,6);
INSERT INTO tblDay VALUES (2283,1333238400,1,4,2012,7);
INSERT INTO tblDay VALUES (2284,1333324800,2,4,2012,1);
INSERT INTO tblDay VALUES (2285,1333411200,3,4,2012,2);
INSERT INTO tblDay VALUES (2286,1333497600,4,4,2012,3);
INSERT INTO tblDay VALUES (2287,1333584000,5,4,2012,4);
INSERT INTO tblDay VALUES (2288,1333670400,6,4,2012,5);
INSERT INTO tblDay VALUES (2289,1333756800,7,4,2012,6);
INSERT INTO tblDay VALUES (2290,1333843200,8,4,2012,7);
INSERT INTO tblDay VALUES (2291,1333929600,9,4,2012,1);
INSERT INTO tblDay VALUES (2292,1334016000,10,4,2012,2);
INSERT INTO tblDay VALUES (2293,1334102400,11,4,2012,3);
INSERT INTO tblDay VALUES (2294,1334188800,12,4,2012,4);
INSERT INTO tblDay VALUES (2295,1334275200,13,4,2012,5);
INSERT INTO tblDay VALUES (2296,1334361600,14,4,2012,6);
INSERT INTO tblDay VALUES (2297,1334448000,15,4,2012,7);
INSERT INTO tblDay VALUES (2298,1334534400,16,4,2012,1);
INSERT INTO tblDay VALUES (2299,1334620800,17,4,2012,2);
INSERT INTO tblDay VALUES (2300,1334707200,18,4,2012,3);
INSERT INTO tblDay VALUES (2301,1334793600,19,4,2012,4);
INSERT INTO tblDay VALUES (2302,1334880000,20,4,2012,5);
INSERT INTO tblDay VALUES (2303,1334966400,21,4,2012,6);
INSERT INTO tblDay VALUES (2304,1335052800,22,4,2012,7);
INSERT INTO tblDay VALUES (2305,1335139200,23,4,2012,1);
INSERT INTO tblDay VALUES (2306,1335225600,24,4,2012,2);
INSERT INTO tblDay VALUES (2307,1335312000,25,4,2012,3);
INSERT INTO tblDay VALUES (2308,1335398400,26,4,2012,4);
INSERT INTO tblDay VALUES (2309,1335484800,27,4,2012,5);
INSERT INTO tblDay VALUES (2310,1335571200,28,4,2012,6);
INSERT INTO tblDay VALUES (2311,1335657600,29,4,2012,7);
INSERT INTO tblDay VALUES (2312,1335744000,30,4,2012,1);
INSERT INTO tblDay VALUES (2313,1335830400,1,5,2012,2);
INSERT INTO tblDay VALUES (2314,1335916800,2,5,2012,3);
INSERT INTO tblDay VALUES (2315,1336003200,3,5,2012,4);
INSERT INTO tblDay VALUES (2316,1336089600,4,5,2012,5);
INSERT INTO tblDay VALUES (2317,1336176000,5,5,2012,6);
INSERT INTO tblDay VALUES (2318,1336262400,6,5,2012,7);
INSERT INTO tblDay VALUES (2319,1336348800,7,5,2012,1);
INSERT INTO tblDay VALUES (2320,1336435200,8,5,2012,2);
INSERT INTO tblDay VALUES (2321,1336521600,9,5,2012,3);
INSERT INTO tblDay VALUES (2322,1336608000,10,5,2012,4);
INSERT INTO tblDay VALUES (2323,1336694400,11,5,2012,5);
INSERT INTO tblDay VALUES (2324,1336780800,12,5,2012,6);
INSERT INTO tblDay VALUES (2325,1336867200,13,5,2012,7);
INSERT INTO tblDay VALUES (2326,1336953600,14,5,2012,1);
INSERT INTO tblDay VALUES (2327,1337040000,15,5,2012,2);
INSERT INTO tblDay VALUES (2328,1337126400,16,5,2012,3);
INSERT INTO tblDay VALUES (2329,1337212800,17,5,2012,4);
INSERT INTO tblDay VALUES (2330,1337299200,18,5,2012,5);
INSERT INTO tblDay VALUES (2331,1337385600,19,5,2012,6);
INSERT INTO tblDay VALUES (2332,1337472000,20,5,2012,7);
INSERT INTO tblDay VALUES (2333,1337558400,21,5,2012,1);
INSERT INTO tblDay VALUES (2334,1337644800,22,5,2012,2);
INSERT INTO tblDay VALUES (2335,1337731200,23,5,2012,3);
INSERT INTO tblDay VALUES (2336,1337817600,24,5,2012,4);
INSERT INTO tblDay VALUES (2337,1337904000,25,5,2012,5);
INSERT INTO tblDay VALUES (2338,1337990400,26,5,2012,6);
INSERT INTO tblDay VALUES (2339,1338076800,27,5,2012,7);
INSERT INTO tblDay VALUES (2340,1338163200,28,5,2012,1);
INSERT INTO tblDay VALUES (2341,1338249600,29,5,2012,2);
INSERT INTO tblDay VALUES (2342,1338336000,30,5,2012,3);
INSERT INTO tblDay VALUES (2343,1338422400,31,5,2012,4);
INSERT INTO tblDay VALUES (2344,1338508800,1,6,2012,5);
INSERT INTO tblDay VALUES (2345,1338595200,2,6,2012,6);
INSERT INTO tblDay VALUES (2346,1338681600,3,6,2012,7);
INSERT INTO tblDay VALUES (2347,1338768000,4,6,2012,1);
INSERT INTO tblDay VALUES (2348,1338854400,5,6,2012,2);
INSERT INTO tblDay VALUES (2349,1338940800,6,6,2012,3);
INSERT INTO tblDay VALUES (2350,1339027200,7,6,2012,4);
INSERT INTO tblDay VALUES (2351,1339113600,8,6,2012,5);
INSERT INTO tblDay VALUES (2352,1339200000,9,6,2012,6);
INSERT INTO tblDay VALUES (2353,1339286400,10,6,2012,7);
INSERT INTO tblDay VALUES (2354,1339372800,11,6,2012,1);
INSERT INTO tblDay VALUES (2355,1339459200,12,6,2012,2);
INSERT INTO tblDay VALUES (2356,1339545600,13,6,2012,3);
INSERT INTO tblDay VALUES (2357,1339632000,14,6,2012,4);
INSERT INTO tblDay VALUES (2358,1339718400,15,6,2012,5);
INSERT INTO tblDay VALUES (2359,1339804800,16,6,2012,6);
INSERT INTO tblDay VALUES (2360,1339891200,17,6,2012,7);
INSERT INTO tblDay VALUES (2361,1339977600,18,6,2012,1);
INSERT INTO tblDay VALUES (2362,1340064000,19,6,2012,2);
INSERT INTO tblDay VALUES (2363,1340150400,20,6,2012,3);
INSERT INTO tblDay VALUES (2364,1340236800,21,6,2012,4);
INSERT INTO tblDay VALUES (2365,1340323200,22,6,2012,5);
INSERT INTO tblDay VALUES (2366,1340409600,23,6,2012,6);
INSERT INTO tblDay VALUES (2367,1340496000,24,6,2012,7);
INSERT INTO tblDay VALUES (2368,1340582400,25,6,2012,1);
INSERT INTO tblDay VALUES (2369,1340668800,26,6,2012,2);
INSERT INTO tblDay VALUES (2370,1340755200,27,6,2012,3);
INSERT INTO tblDay VALUES (2371,1340841600,28,6,2012,4);
INSERT INTO tblDay VALUES (2372,1340928000,29,6,2012,5);
INSERT INTO tblDay VALUES (2373,1341014400,30,6,2012,6);
INSERT INTO tblDay VALUES (2374,1341100800,1,7,2012,7);
INSERT INTO tblDay VALUES (2375,1341187200,2,7,2012,1);
INSERT INTO tblDay VALUES (2376,1341273600,3,7,2012,2);
INSERT INTO tblDay VALUES (2377,1341360000,4,7,2012,3);
INSERT INTO tblDay VALUES (2378,1341446400,5,7,2012,4);
INSERT INTO tblDay VALUES (2379,1341532800,6,7,2012,5);
INSERT INTO tblDay VALUES (2380,1341619200,7,7,2012,6);
INSERT INTO tblDay VALUES (2381,1341705600,8,7,2012,7);
INSERT INTO tblDay VALUES (2382,1341792000,9,7,2012,1);
INSERT INTO tblDay VALUES (2383,1341878400,10,7,2012,2);
INSERT INTO tblDay VALUES (2384,1341964800,11,7,2012,3);
INSERT INTO tblDay VALUES (2385,1342051200,12,7,2012,4);
INSERT INTO tblDay VALUES (2386,1342137600,13,7,2012,5);
INSERT INTO tblDay VALUES (2387,1342224000,14,7,2012,6);
INSERT INTO tblDay VALUES (2388,1342310400,15,7,2012,7);
INSERT INTO tblDay VALUES (2389,1342396800,16,7,2012,1);
INSERT INTO tblDay VALUES (2390,1342483200,17,7,2012,2);
INSERT INTO tblDay VALUES (2391,1342569600,18,7,2012,3);
INSERT INTO tblDay VALUES (2392,1342656000,19,7,2012,4);
INSERT INTO tblDay VALUES (2393,1342742400,20,7,2012,5);
INSERT INTO tblDay VALUES (2394,1342828800,21,7,2012,6);
INSERT INTO tblDay VALUES (2395,1342915200,22,7,2012,7);
INSERT INTO tblDay VALUES (2396,1343001600,23,7,2012,1);
INSERT INTO tblDay VALUES (2397,1343088000,24,7,2012,2);
INSERT INTO tblDay VALUES (2398,1343174400,25,7,2012,3);
INSERT INTO tblDay VALUES (2399,1343260800,26,7,2012,4);
INSERT INTO tblDay VALUES (2400,1343347200,27,7,2012,5);
INSERT INTO tblDay VALUES (2401,1343433600,28,7,2012,6);
INSERT INTO tblDay VALUES (2402,1343520000,29,7,2012,7);
INSERT INTO tblDay VALUES (2403,1343606400,30,7,2012,1);
INSERT INTO tblDay VALUES (2404,1343692800,31,7,2012,2);
INSERT INTO tblDay VALUES (2405,1343779200,1,8,2012,3);
INSERT INTO tblDay VALUES (2406,1343865600,2,8,2012,4);
INSERT INTO tblDay VALUES (2407,1343952000,3,8,2012,5);
INSERT INTO tblDay VALUES (2408,1344038400,4,8,2012,6);
INSERT INTO tblDay VALUES (2409,1344124800,5,8,2012,7);
INSERT INTO tblDay VALUES (2410,1344211200,6,8,2012,1);
INSERT INTO tblDay VALUES (2411,1344297600,7,8,2012,2);
INSERT INTO tblDay VALUES (2412,1344384000,8,8,2012,3);
INSERT INTO tblDay VALUES (2413,1344470400,9,8,2012,4);
INSERT INTO tblDay VALUES (2414,1344556800,10,8,2012,5);
INSERT INTO tblDay VALUES (2415,1344643200,11,8,2012,6);
INSERT INTO tblDay VALUES (2416,1344729600,12,8,2012,7);
INSERT INTO tblDay VALUES (2417,1344816000,13,8,2012,1);
INSERT INTO tblDay VALUES (2418,1344902400,14,8,2012,2);
INSERT INTO tblDay VALUES (2419,1344988800,15,8,2012,3);
INSERT INTO tblDay VALUES (2420,1345075200,16,8,2012,4);
INSERT INTO tblDay VALUES (2421,1345161600,17,8,2012,5);
INSERT INTO tblDay VALUES (2422,1345248000,18,8,2012,6);
INSERT INTO tblDay VALUES (2423,1345334400,19,8,2012,7);
INSERT INTO tblDay VALUES (2424,1345420800,20,8,2012,1);
INSERT INTO tblDay VALUES (2425,1345507200,21,8,2012,2);
INSERT INTO tblDay VALUES (2426,1345593600,22,8,2012,3);
INSERT INTO tblDay VALUES (2427,1345680000,23,8,2012,4);
INSERT INTO tblDay VALUES (2428,1345766400,24,8,2012,5);
INSERT INTO tblDay VALUES (2429,1345852800,25,8,2012,6);
INSERT INTO tblDay VALUES (2430,1345939200,26,8,2012,7);
INSERT INTO tblDay VALUES (2431,1346025600,27,8,2012,1);
INSERT INTO tblDay VALUES (2432,1346112000,28,8,2012,2);
INSERT INTO tblDay VALUES (2433,1346198400,29,8,2012,3);
INSERT INTO tblDay VALUES (2434,1346284800,30,8,2012,4);
INSERT INTO tblDay VALUES (2435,1346371200,31,8,2012,5);
INSERT INTO tblDay VALUES (2436,1346457600,1,9,2012,6);
INSERT INTO tblDay VALUES (2437,1346544000,2,9,2012,7);
INSERT INTO tblDay VALUES (2438,1346630400,3,9,2012,1);
INSERT INTO tblDay VALUES (2439,1346716800,4,9,2012,2);
INSERT INTO tblDay VALUES (2440,1346803200,5,9,2012,3);
INSERT INTO tblDay VALUES (2441,1346889600,6,9,2012,4);
INSERT INTO tblDay VALUES (2442,1346976000,7,9,2012,5);
INSERT INTO tblDay VALUES (2443,1347062400,8,9,2012,6);
INSERT INTO tblDay VALUES (2444,1347148800,9,9,2012,7);
INSERT INTO tblDay VALUES (2445,1347235200,10,9,2012,1);
INSERT INTO tblDay VALUES (2446,1347321600,11,9,2012,2);
INSERT INTO tblDay VALUES (2447,1347408000,12,9,2012,3);
INSERT INTO tblDay VALUES (2448,1347494400,13,9,2012,4);
INSERT INTO tblDay VALUES (2449,1347580800,14,9,2012,5);
INSERT INTO tblDay VALUES (2450,1347667200,15,9,2012,6);
INSERT INTO tblDay VALUES (2451,1347753600,16,9,2012,7);
INSERT INTO tblDay VALUES (2452,1347840000,17,9,2012,1);
INSERT INTO tblDay VALUES (2453,1347926400,18,9,2012,2);
INSERT INTO tblDay VALUES (2454,1348012800,19,9,2012,3);
INSERT INTO tblDay VALUES (2455,1348099200,20,9,2012,4);
INSERT INTO tblDay VALUES (2456,1348185600,21,9,2012,5);
INSERT INTO tblDay VALUES (2457,1348272000,22,9,2012,6);
INSERT INTO tblDay VALUES (2458,1348358400,23,9,2012,7);
INSERT INTO tblDay VALUES (2459,1348444800,24,9,2012,1);
INSERT INTO tblDay VALUES (2460,1348531200,25,9,2012,2);
INSERT INTO tblDay VALUES (2461,1348617600,26,9,2012,3);
INSERT INTO tblDay VALUES (2462,1348704000,27,9,2012,4);
INSERT INTO tblDay VALUES (2463,1348790400,28,9,2012,5);
INSERT INTO tblDay VALUES (2464,1348876800,29,9,2012,6);
INSERT INTO tblDay VALUES (2465,1348963200,30,9,2012,7);
INSERT INTO tblDay VALUES (2466,1349049600,1,10,2012,1);
INSERT INTO tblDay VALUES (2467,1349136000,2,10,2012,2);
INSERT INTO tblDay VALUES (2468,1349222400,3,10,2012,3);
INSERT INTO tblDay VALUES (2469,1349308800,4,10,2012,4);
INSERT INTO tblDay VALUES (2470,1349395200,5,10,2012,5);
INSERT INTO tblDay VALUES (2471,1349481600,6,10,2012,6);
INSERT INTO tblDay VALUES (2472,1349568000,7,10,2012,7);
INSERT INTO tblDay VALUES (2473,1349654400,8,10,2012,1);
INSERT INTO tblDay VALUES (2474,1349740800,9,10,2012,2);
INSERT INTO tblDay VALUES (2475,1349827200,10,10,2012,3);
INSERT INTO tblDay VALUES (2476,1349913600,11,10,2012,4);
INSERT INTO tblDay VALUES (2477,1350000000,12,10,2012,5);
INSERT INTO tblDay VALUES (2478,1350086400,13,10,2012,6);
INSERT INTO tblDay VALUES (2479,1350172800,14,10,2012,7);
INSERT INTO tblDay VALUES (2480,1350259200,15,10,2012,1);
INSERT INTO tblDay VALUES (2481,1350345600,16,10,2012,2);
INSERT INTO tblDay VALUES (2482,1350432000,17,10,2012,3);
INSERT INTO tblDay VALUES (2483,1350518400,18,10,2012,4);
INSERT INTO tblDay VALUES (2484,1350604800,19,10,2012,5);
INSERT INTO tblDay VALUES (2485,1350691200,20,10,2012,6);
INSERT INTO tblDay VALUES (2486,1350777600,21,10,2012,7);
INSERT INTO tblDay VALUES (2487,1350864000,22,10,2012,1);
INSERT INTO tblDay VALUES (2488,1350950400,23,10,2012,2);
INSERT INTO tblDay VALUES (2489,1351036800,24,10,2012,3);
INSERT INTO tblDay VALUES (2490,1351123200,25,10,2012,4);
INSERT INTO tblDay VALUES (2491,1351209600,26,10,2012,5);
INSERT INTO tblDay VALUES (2492,1351296000,27,10,2012,6);
INSERT INTO tblDay VALUES (2493,1351382400,28,10,2012,7);
INSERT INTO tblDay VALUES (2494,1351468800,29,10,2012,1);
INSERT INTO tblDay VALUES (2495,1351555200,30,10,2012,2);
INSERT INTO tblDay VALUES (2496,1351641600,31,10,2012,3);
INSERT INTO tblDay VALUES (2497,1351728000,1,11,2012,4);
INSERT INTO tblDay VALUES (2498,1351814400,2,11,2012,5);
INSERT INTO tblDay VALUES (2499,1351900800,3,11,2012,6);
INSERT INTO tblDay VALUES (2500,1351987200,4,11,2012,7);
INSERT INTO tblDay VALUES (2501,1352073600,5,11,2012,1);
INSERT INTO tblDay VALUES (2502,1352160000,6,11,2012,2);
INSERT INTO tblDay VALUES (2503,1352246400,7,11,2012,3);
INSERT INTO tblDay VALUES (2504,1352332800,8,11,2012,4);
INSERT INTO tblDay VALUES (2505,1352419200,9,11,2012,5);
INSERT INTO tblDay VALUES (2506,1352505600,10,11,2012,6);
INSERT INTO tblDay VALUES (2507,1352592000,11,11,2012,7);
INSERT INTO tblDay VALUES (2508,1352678400,12,11,2012,1);
INSERT INTO tblDay VALUES (2509,1352764800,13,11,2012,2);
INSERT INTO tblDay VALUES (2510,1352851200,14,11,2012,3);
INSERT INTO tblDay VALUES (2511,1352937600,15,11,2012,4);
INSERT INTO tblDay VALUES (2512,1353024000,16,11,2012,5);
INSERT INTO tblDay VALUES (2513,1353110400,17,11,2012,6);
INSERT INTO tblDay VALUES (2514,1353196800,18,11,2012,7);
INSERT INTO tblDay VALUES (2515,1353283200,19,11,2012,1);
INSERT INTO tblDay VALUES (2516,1353369600,20,11,2012,2);
INSERT INTO tblDay VALUES (2517,1353456000,21,11,2012,3);
INSERT INTO tblDay VALUES (2518,1353542400,22,11,2012,4);
INSERT INTO tblDay VALUES (2519,1353628800,23,11,2012,5);
INSERT INTO tblDay VALUES (2520,1353715200,24,11,2012,6);
INSERT INTO tblDay VALUES (2521,1353801600,25,11,2012,7);
INSERT INTO tblDay VALUES (2522,1353888000,26,11,2012,1);
INSERT INTO tblDay VALUES (2523,1353974400,27,11,2012,2);
INSERT INTO tblDay VALUES (2524,1354060800,28,11,2012,3);
INSERT INTO tblDay VALUES (2525,1354147200,29,11,2012,4);
INSERT INTO tblDay VALUES (2526,1354233600,30,11,2012,5);
INSERT INTO tblDay VALUES (2527,1354320000,1,12,2012,6);
INSERT INTO tblDay VALUES (2528,1354406400,2,12,2012,7);
INSERT INTO tblDay VALUES (2529,1354492800,3,12,2012,1);
INSERT INTO tblDay VALUES (2530,1354579200,4,12,2012,2);
INSERT INTO tblDay VALUES (2531,1354665600,5,12,2012,3);
INSERT INTO tblDay VALUES (2532,1354752000,6,12,2012,4);
INSERT INTO tblDay VALUES (2533,1354838400,7,12,2012,5);
INSERT INTO tblDay VALUES (2534,1354924800,8,12,2012,6);
INSERT INTO tblDay VALUES (2535,1355011200,9,12,2012,7);
INSERT INTO tblDay VALUES (2536,1355097600,10,12,2012,1);
INSERT INTO tblDay VALUES (2537,1355184000,11,12,2012,2);
INSERT INTO tblDay VALUES (2538,1355270400,12,12,2012,3);
INSERT INTO tblDay VALUES (2539,1355356800,13,12,2012,4);
INSERT INTO tblDay VALUES (2540,1355443200,14,12,2012,5);
INSERT INTO tblDay VALUES (2541,1355529600,15,12,2012,6);
INSERT INTO tblDay VALUES (2542,1355616000,16,12,2012,7);
INSERT INTO tblDay VALUES (2543,1355702400,17,12,2012,1);
INSERT INTO tblDay VALUES (2544,1355788800,18,12,2012,2);
INSERT INTO tblDay VALUES (2545,1355875200,19,12,2012,3);
INSERT INTO tblDay VALUES (2546,1355961600,20,12,2012,4);
INSERT INTO tblDay VALUES (2547,1356048000,21,12,2012,5);
INSERT INTO tblDay VALUES (2548,1356134400,22,12,2012,6);
INSERT INTO tblDay VALUES (2549,1356220800,23,12,2012,7);
INSERT INTO tblDay VALUES (2550,1356307200,24,12,2012,1);
INSERT INTO tblDay VALUES (2551,1356393600,25,12,2012,2);
INSERT INTO tblDay VALUES (2552,1356480000,26,12,2012,3);
INSERT INTO tblDay VALUES (2553,1356566400,27,12,2012,4);
INSERT INTO tblDay VALUES (2554,1356652800,28,12,2012,5);
INSERT INTO tblDay VALUES (2555,1356739200,29,12,2012,6);
INSERT INTO tblDay VALUES (2556,1356825600,30,12,2012,7);
INSERT INTO tblDay VALUES (2557,1356912000,31,12,2012,1);

--
-- days from 2013 until 2016
--
INSERT INTO tblDay VALUES (2558,1356998400,1,1,2013,1);
INSERT INTO tblDay VALUES (2559,1357084800,2,1,2013,1);
INSERT INTO tblDay VALUES (2560,1357171200,3,1,2013,1);
INSERT INTO tblDay VALUES (2561,1357257600,4,1,2013,1);
INSERT INTO tblDay VALUES (2562,1357344000,5,1,2013,1);
INSERT INTO tblDay VALUES (2563,1357430400,6,1,2013,1);
INSERT INTO tblDay VALUES (2564,1357516800,7,1,2013,1);
INSERT INTO tblDay VALUES (2565,1357603200,8,1,2013,1);
INSERT INTO tblDay VALUES (2566,1357689600,9,1,2013,1);
INSERT INTO tblDay VALUES (2567,1357776000,10,1,2013,1);
INSERT INTO tblDay VALUES (2568,1357862400,11,1,2013,1);
INSERT INTO tblDay VALUES (2569,1357948800,12,1,2013,1);
INSERT INTO tblDay VALUES (2570,1358035200,13,1,2013,1);
INSERT INTO tblDay VALUES (2571,1358121600,14,1,2013,1);
INSERT INTO tblDay VALUES (2572,1358208000,15,1,2013,1);
INSERT INTO tblDay VALUES (2573,1358294400,16,1,2013,1);
INSERT INTO tblDay VALUES (2574,1358380800,17,1,2013,1);
INSERT INTO tblDay VALUES (2575,1358467200,18,1,2013,1);
INSERT INTO tblDay VALUES (2576,1358553600,19,1,2013,1);
INSERT INTO tblDay VALUES (2577,1358640000,20,1,2013,1);
INSERT INTO tblDay VALUES (2578,1358726400,21,1,2013,1);
INSERT INTO tblDay VALUES (2579,1358812800,22,1,2013,1);
INSERT INTO tblDay VALUES (2580,1358899200,23,1,2013,1);
INSERT INTO tblDay VALUES (2581,1358985600,24,1,2013,1);
INSERT INTO tblDay VALUES (2582,1359072000,25,1,2013,1);
INSERT INTO tblDay VALUES (2583,1359158400,26,1,2013,1);
INSERT INTO tblDay VALUES (2584,1359244800,27,1,2013,1);
INSERT INTO tblDay VALUES (2585,1359331200,28,1,2013,1);
INSERT INTO tblDay VALUES (2586,1359417600,29,1,2013,1);
INSERT INTO tblDay VALUES (2587,1359504000,30,1,2013,1);
INSERT INTO tblDay VALUES (2588,1359590400,31,1,2013,1);
INSERT INTO tblDay VALUES (2589,1359676800,1,2,2013,1);
INSERT INTO tblDay VALUES (2590,1359763200,2,2,2013,1);
INSERT INTO tblDay VALUES (2591,1359849600,3,2,2013,1);
INSERT INTO tblDay VALUES (2592,1359936000,4,2,2013,1);
INSERT INTO tblDay VALUES (2593,1360022400,5,2,2013,1);
INSERT INTO tblDay VALUES (2594,1360108800,6,2,2013,1);
INSERT INTO tblDay VALUES (2595,1360195200,7,2,2013,1);
INSERT INTO tblDay VALUES (2596,1360281600,8,2,2013,1);
INSERT INTO tblDay VALUES (2597,1360368000,9,2,2013,1);
INSERT INTO tblDay VALUES (2598,1360454400,10,2,2013,1);
INSERT INTO tblDay VALUES (2599,1360540800,11,2,2013,1);
INSERT INTO tblDay VALUES (2600,1360627200,12,2,2013,1);
INSERT INTO tblDay VALUES (2601,1360713600,13,2,2013,1);
INSERT INTO tblDay VALUES (2602,1360800000,14,2,2013,1);
INSERT INTO tblDay VALUES (2603,1360886400,15,2,2013,1);
INSERT INTO tblDay VALUES (2604,1360972800,16,2,2013,1);
INSERT INTO tblDay VALUES (2605,1361059200,17,2,2013,1);
INSERT INTO tblDay VALUES (2606,1361145600,18,2,2013,1);
INSERT INTO tblDay VALUES (2607,1361232000,19,2,2013,1);
INSERT INTO tblDay VALUES (2608,1361318400,20,2,2013,1);
INSERT INTO tblDay VALUES (2609,1361404800,21,2,2013,1);
INSERT INTO tblDay VALUES (2610,1361491200,22,2,2013,1);
INSERT INTO tblDay VALUES (2611,1361577600,23,2,2013,1);
INSERT INTO tblDay VALUES (2612,1361664000,24,2,2013,1);
INSERT INTO tblDay VALUES (2613,1361750400,25,2,2013,1);
INSERT INTO tblDay VALUES (2614,1361836800,26,2,2013,1);
INSERT INTO tblDay VALUES (2615,1361923200,27,2,2013,1);
INSERT INTO tblDay VALUES (2616,1362009600,28,2,2013,1);
INSERT INTO tblDay VALUES (2617,1362096000,1,3,2013,1);
INSERT INTO tblDay VALUES (2618,1362182400,2,3,2013,1);
INSERT INTO tblDay VALUES (2619,1362268800,3,3,2013,1);
INSERT INTO tblDay VALUES (2620,1362355200,4,3,2013,1);
INSERT INTO tblDay VALUES (2621,1362441600,5,3,2013,1);
INSERT INTO tblDay VALUES (2622,1362528000,6,3,2013,1);
INSERT INTO tblDay VALUES (2623,1362614400,7,3,2013,1);
INSERT INTO tblDay VALUES (2624,1362700800,8,3,2013,1);
INSERT INTO tblDay VALUES (2625,1362787200,9,3,2013,1);
INSERT INTO tblDay VALUES (2626,1362873600,10,3,2013,1);
INSERT INTO tblDay VALUES (2627,1362960000,11,3,2013,1);
INSERT INTO tblDay VALUES (2628,1363046400,12,3,2013,1);
INSERT INTO tblDay VALUES (2629,1363132800,13,3,2013,1);
INSERT INTO tblDay VALUES (2630,1363219200,14,3,2013,1);
INSERT INTO tblDay VALUES (2631,1363305600,15,3,2013,1);
INSERT INTO tblDay VALUES (2632,1363392000,16,3,2013,1);
INSERT INTO tblDay VALUES (2633,1363478400,17,3,2013,1);
INSERT INTO tblDay VALUES (2634,1363564800,18,3,2013,1);
INSERT INTO tblDay VALUES (2635,1363651200,19,3,2013,1);
INSERT INTO tblDay VALUES (2636,1363737600,20,3,2013,1);
INSERT INTO tblDay VALUES (2637,1363824000,21,3,2013,1);
INSERT INTO tblDay VALUES (2638,1363910400,22,3,2013,1);
INSERT INTO tblDay VALUES (2639,1363996800,23,3,2013,1);
INSERT INTO tblDay VALUES (2640,1364083200,24,3,2013,1);
INSERT INTO tblDay VALUES (2641,1364169600,25,3,2013,1);
INSERT INTO tblDay VALUES (2642,1364256000,26,3,2013,1);
INSERT INTO tblDay VALUES (2643,1364342400,27,3,2013,1);
INSERT INTO tblDay VALUES (2644,1364428800,28,3,2013,1);
INSERT INTO tblDay VALUES (2645,1364515200,29,3,2013,1);
INSERT INTO tblDay VALUES (2646,1364601600,30,3,2013,1);
INSERT INTO tblDay VALUES (2647,1364688000,31,3,2013,1);
INSERT INTO tblDay VALUES (2648,1364774400,1,4,2013,1);
INSERT INTO tblDay VALUES (2649,1364860800,2,4,2013,1);
INSERT INTO tblDay VALUES (2650,1364947200,3,4,2013,1);
INSERT INTO tblDay VALUES (2651,1365033600,4,4,2013,1);
INSERT INTO tblDay VALUES (2652,1365120000,5,4,2013,1);
INSERT INTO tblDay VALUES (2653,1365206400,6,4,2013,1);
INSERT INTO tblDay VALUES (2654,1365292800,7,4,2013,1);
INSERT INTO tblDay VALUES (2655,1365379200,8,4,2013,1);
INSERT INTO tblDay VALUES (2656,1365465600,9,4,2013,1);
INSERT INTO tblDay VALUES (2657,1365552000,10,4,2013,1);
INSERT INTO tblDay VALUES (2658,1365638400,11,4,2013,1);
INSERT INTO tblDay VALUES (2659,1365724800,12,4,2013,1);
INSERT INTO tblDay VALUES (2660,1365811200,13,4,2013,1);
INSERT INTO tblDay VALUES (2661,1365897600,14,4,2013,1);
INSERT INTO tblDay VALUES (2662,1365984000,15,4,2013,1);
INSERT INTO tblDay VALUES (2663,1366070400,16,4,2013,1);
INSERT INTO tblDay VALUES (2664,1366156800,17,4,2013,1);
INSERT INTO tblDay VALUES (2665,1366243200,18,4,2013,1);
INSERT INTO tblDay VALUES (2666,1366329600,19,4,2013,1);
INSERT INTO tblDay VALUES (2667,1366416000,20,4,2013,1);
INSERT INTO tblDay VALUES (2668,1366502400,21,4,2013,1);
INSERT INTO tblDay VALUES (2669,1366588800,22,4,2013,1);
INSERT INTO tblDay VALUES (2670,1366675200,23,4,2013,1);
INSERT INTO tblDay VALUES (2671,1366761600,24,4,2013,1);
INSERT INTO tblDay VALUES (2672,1366848000,25,4,2013,1);
INSERT INTO tblDay VALUES (2673,1366934400,26,4,2013,1);
INSERT INTO tblDay VALUES (2674,1367020800,27,4,2013,1);
INSERT INTO tblDay VALUES (2675,1367107200,28,4,2013,1);
INSERT INTO tblDay VALUES (2676,1367193600,29,4,2013,1);
INSERT INTO tblDay VALUES (2677,1367280000,30,4,2013,1);
INSERT INTO tblDay VALUES (2678,1367366400,1,5,2013,1);
INSERT INTO tblDay VALUES (2679,1367452800,2,5,2013,1);
INSERT INTO tblDay VALUES (2680,1367539200,3,5,2013,1);
INSERT INTO tblDay VALUES (2681,1367625600,4,5,2013,1);
INSERT INTO tblDay VALUES (2682,1367712000,5,5,2013,1);
INSERT INTO tblDay VALUES (2683,1367798400,6,5,2013,1);
INSERT INTO tblDay VALUES (2684,1367884800,7,5,2013,1);
INSERT INTO tblDay VALUES (2685,1367971200,8,5,2013,1);
INSERT INTO tblDay VALUES (2686,1368057600,9,5,2013,1);
INSERT INTO tblDay VALUES (2687,1368144000,10,5,2013,1);
INSERT INTO tblDay VALUES (2688,1368230400,11,5,2013,1);
INSERT INTO tblDay VALUES (2689,1368316800,12,5,2013,1);
INSERT INTO tblDay VALUES (2690,1368403200,13,5,2013,1);
INSERT INTO tblDay VALUES (2691,1368489600,14,5,2013,1);
INSERT INTO tblDay VALUES (2692,1368576000,15,5,2013,1);
INSERT INTO tblDay VALUES (2693,1368662400,16,5,2013,1);
INSERT INTO tblDay VALUES (2694,1368748800,17,5,2013,1);
INSERT INTO tblDay VALUES (2695,1368835200,18,5,2013,1);
INSERT INTO tblDay VALUES (2696,1368921600,19,5,2013,1);
INSERT INTO tblDay VALUES (2697,1369008000,20,5,2013,1);
INSERT INTO tblDay VALUES (2698,1369094400,21,5,2013,1);
INSERT INTO tblDay VALUES (2699,1369180800,22,5,2013,1);
INSERT INTO tblDay VALUES (2700,1369267200,23,5,2013,1);
INSERT INTO tblDay VALUES (2701,1369353600,24,5,2013,1);
INSERT INTO tblDay VALUES (2702,1369440000,25,5,2013,1);
INSERT INTO tblDay VALUES (2703,1369526400,26,5,2013,1);
INSERT INTO tblDay VALUES (2704,1369612800,27,5,2013,1);
INSERT INTO tblDay VALUES (2705,1369699200,28,5,2013,1);
INSERT INTO tblDay VALUES (2706,1369785600,29,5,2013,1);
INSERT INTO tblDay VALUES (2707,1369872000,30,5,2013,1);
INSERT INTO tblDay VALUES (2708,1369958400,31,5,2013,1);
INSERT INTO tblDay VALUES (2709,1370044800,1,6,2013,1);
INSERT INTO tblDay VALUES (2710,1370131200,2,6,2013,1);
INSERT INTO tblDay VALUES (2711,1370217600,3,6,2013,1);
INSERT INTO tblDay VALUES (2712,1370304000,4,6,2013,1);
INSERT INTO tblDay VALUES (2713,1370390400,5,6,2013,1);
INSERT INTO tblDay VALUES (2714,1370476800,6,6,2013,1);
INSERT INTO tblDay VALUES (2715,1370563200,7,6,2013,1);
INSERT INTO tblDay VALUES (2716,1370649600,8,6,2013,1);
INSERT INTO tblDay VALUES (2717,1370736000,9,6,2013,1);
INSERT INTO tblDay VALUES (2718,1370822400,10,6,2013,1);
INSERT INTO tblDay VALUES (2719,1370908800,11,6,2013,1);
INSERT INTO tblDay VALUES (2720,1370995200,12,6,2013,1);
INSERT INTO tblDay VALUES (2721,1371081600,13,6,2013,1);
INSERT INTO tblDay VALUES (2722,1371168000,14,6,2013,1);
INSERT INTO tblDay VALUES (2723,1371254400,15,6,2013,1);
INSERT INTO tblDay VALUES (2724,1371340800,16,6,2013,1);
INSERT INTO tblDay VALUES (2725,1371427200,17,6,2013,1);
INSERT INTO tblDay VALUES (2726,1371513600,18,6,2013,1);
INSERT INTO tblDay VALUES (2727,1371600000,19,6,2013,1);
INSERT INTO tblDay VALUES (2728,1371686400,20,6,2013,1);
INSERT INTO tblDay VALUES (2729,1371772800,21,6,2013,1);
INSERT INTO tblDay VALUES (2730,1371859200,22,6,2013,1);
INSERT INTO tblDay VALUES (2731,1371945600,23,6,2013,1);
INSERT INTO tblDay VALUES (2732,1372032000,24,6,2013,1);
INSERT INTO tblDay VALUES (2733,1372118400,25,6,2013,1);
INSERT INTO tblDay VALUES (2734,1372204800,26,6,2013,1);
INSERT INTO tblDay VALUES (2735,1372291200,27,6,2013,1);
INSERT INTO tblDay VALUES (2736,1372377600,28,6,2013,1);
INSERT INTO tblDay VALUES (2737,1372464000,29,6,2013,1);
INSERT INTO tblDay VALUES (2738,1372550400,30,6,2013,1);
INSERT INTO tblDay VALUES (2739,1372636800,1,7,2013,1);
INSERT INTO tblDay VALUES (2740,1372723200,2,7,2013,1);
INSERT INTO tblDay VALUES (2741,1372809600,3,7,2013,1);
INSERT INTO tblDay VALUES (2742,1372896000,4,7,2013,1);
INSERT INTO tblDay VALUES (2743,1372982400,5,7,2013,1);
INSERT INTO tblDay VALUES (2744,1373068800,6,7,2013,1);
INSERT INTO tblDay VALUES (2745,1373155200,7,7,2013,1);
INSERT INTO tblDay VALUES (2746,1373241600,8,7,2013,1);
INSERT INTO tblDay VALUES (2747,1373328000,9,7,2013,1);
INSERT INTO tblDay VALUES (2748,1373414400,10,7,2013,1);
INSERT INTO tblDay VALUES (2749,1373500800,11,7,2013,1);
INSERT INTO tblDay VALUES (2750,1373587200,12,7,2013,1);
INSERT INTO tblDay VALUES (2751,1373673600,13,7,2013,1);
INSERT INTO tblDay VALUES (2752,1373760000,14,7,2013,1);
INSERT INTO tblDay VALUES (2753,1373846400,15,7,2013,1);
INSERT INTO tblDay VALUES (2754,1373932800,16,7,2013,1);
INSERT INTO tblDay VALUES (2755,1374019200,17,7,2013,1);
INSERT INTO tblDay VALUES (2756,1374105600,18,7,2013,1);
INSERT INTO tblDay VALUES (2757,1374192000,19,7,2013,1);
INSERT INTO tblDay VALUES (2758,1374278400,20,7,2013,1);
INSERT INTO tblDay VALUES (2759,1374364800,21,7,2013,1);
INSERT INTO tblDay VALUES (2760,1374451200,22,7,2013,1);
INSERT INTO tblDay VALUES (2761,1374537600,23,7,2013,1);
INSERT INTO tblDay VALUES (2762,1374624000,24,7,2013,1);
INSERT INTO tblDay VALUES (2763,1374710400,25,7,2013,1);
INSERT INTO tblDay VALUES (2764,1374796800,26,7,2013,1);
INSERT INTO tblDay VALUES (2765,1374883200,27,7,2013,1);
INSERT INTO tblDay VALUES (2766,1374969600,28,7,2013,1);
INSERT INTO tblDay VALUES (2767,1375056000,29,7,2013,1);
INSERT INTO tblDay VALUES (2768,1375142400,30,7,2013,1);
INSERT INTO tblDay VALUES (2769,1375228800,31,7,2013,1);
INSERT INTO tblDay VALUES (2770,1375315200,1,8,2013,1);
INSERT INTO tblDay VALUES (2771,1375401600,2,8,2013,1);
INSERT INTO tblDay VALUES (2772,1375488000,3,8,2013,1);
INSERT INTO tblDay VALUES (2773,1375574400,4,8,2013,1);
INSERT INTO tblDay VALUES (2774,1375660800,5,8,2013,1);
INSERT INTO tblDay VALUES (2775,1375747200,6,8,2013,1);
INSERT INTO tblDay VALUES (2776,1375833600,7,8,2013,1);
INSERT INTO tblDay VALUES (2777,1375920000,8,8,2013,1);
INSERT INTO tblDay VALUES (2778,1376006400,9,8,2013,1);
INSERT INTO tblDay VALUES (2779,1376092800,10,8,2013,1);
INSERT INTO tblDay VALUES (2780,1376179200,11,8,2013,1);
INSERT INTO tblDay VALUES (2781,1376265600,12,8,2013,1);
INSERT INTO tblDay VALUES (2782,1376352000,13,8,2013,1);
INSERT INTO tblDay VALUES (2783,1376438400,14,8,2013,1);
INSERT INTO tblDay VALUES (2784,1376524800,15,8,2013,1);
INSERT INTO tblDay VALUES (2785,1376611200,16,8,2013,1);
INSERT INTO tblDay VALUES (2786,1376697600,17,8,2013,1);
INSERT INTO tblDay VALUES (2787,1376784000,18,8,2013,1);
INSERT INTO tblDay VALUES (2788,1376870400,19,8,2013,1);
INSERT INTO tblDay VALUES (2789,1376956800,20,8,2013,1);
INSERT INTO tblDay VALUES (2790,1377043200,21,8,2013,1);
INSERT INTO tblDay VALUES (2791,1377129600,22,8,2013,1);
INSERT INTO tblDay VALUES (2792,1377216000,23,8,2013,1);
INSERT INTO tblDay VALUES (2793,1377302400,24,8,2013,1);
INSERT INTO tblDay VALUES (2794,1377388800,25,8,2013,1);
INSERT INTO tblDay VALUES (2795,1377475200,26,8,2013,1);
INSERT INTO tblDay VALUES (2796,1377561600,27,8,2013,1);
INSERT INTO tblDay VALUES (2797,1377648000,28,8,2013,1);
INSERT INTO tblDay VALUES (2798,1377734400,29,8,2013,1);
INSERT INTO tblDay VALUES (2799,1377820800,30,8,2013,1);
INSERT INTO tblDay VALUES (2800,1377907200,31,8,2013,1);
INSERT INTO tblDay VALUES (2801,1377993600,1,9,2013,1);
INSERT INTO tblDay VALUES (2802,1378080000,2,9,2013,1);
INSERT INTO tblDay VALUES (2803,1378166400,3,9,2013,1);
INSERT INTO tblDay VALUES (2804,1378252800,4,9,2013,1);
INSERT INTO tblDay VALUES (2805,1378339200,5,9,2013,1);
INSERT INTO tblDay VALUES (2806,1378425600,6,9,2013,1);
INSERT INTO tblDay VALUES (2807,1378512000,7,9,2013,1);
INSERT INTO tblDay VALUES (2808,1378598400,8,9,2013,1);
INSERT INTO tblDay VALUES (2809,1378684800,9,9,2013,1);
INSERT INTO tblDay VALUES (2810,1378771200,10,9,2013,1);
INSERT INTO tblDay VALUES (2811,1378857600,11,9,2013,1);
INSERT INTO tblDay VALUES (2812,1378944000,12,9,2013,1);
INSERT INTO tblDay VALUES (2813,1379030400,13,9,2013,1);
INSERT INTO tblDay VALUES (2814,1379116800,14,9,2013,1);
INSERT INTO tblDay VALUES (2815,1379203200,15,9,2013,1);
INSERT INTO tblDay VALUES (2816,1379289600,16,9,2013,1);
INSERT INTO tblDay VALUES (2817,1379376000,17,9,2013,1);
INSERT INTO tblDay VALUES (2818,1379462400,18,9,2013,1);
INSERT INTO tblDay VALUES (2819,1379548800,19,9,2013,1);
INSERT INTO tblDay VALUES (2820,1379635200,20,9,2013,1);
INSERT INTO tblDay VALUES (2821,1379721600,21,9,2013,1);
INSERT INTO tblDay VALUES (2822,1379808000,22,9,2013,1);
INSERT INTO tblDay VALUES (2823,1379894400,23,9,2013,1);
INSERT INTO tblDay VALUES (2824,1379980800,24,9,2013,1);
INSERT INTO tblDay VALUES (2825,1380067200,25,9,2013,1);
INSERT INTO tblDay VALUES (2826,1380153600,26,9,2013,1);
INSERT INTO tblDay VALUES (2827,1380240000,27,9,2013,1);
INSERT INTO tblDay VALUES (2828,1380326400,28,9,2013,1);
INSERT INTO tblDay VALUES (2829,1380412800,29,9,2013,1);
INSERT INTO tblDay VALUES (2830,1380499200,30,9,2013,1);
INSERT INTO tblDay VALUES (2831,1380585600,1,10,2013,1);
INSERT INTO tblDay VALUES (2832,1380672000,2,10,2013,1);
INSERT INTO tblDay VALUES (2833,1380758400,3,10,2013,1);
INSERT INTO tblDay VALUES (2834,1380844800,4,10,2013,1);
INSERT INTO tblDay VALUES (2835,1380931200,5,10,2013,1);
INSERT INTO tblDay VALUES (2836,1381017600,6,10,2013,1);
INSERT INTO tblDay VALUES (2837,1381104000,7,10,2013,1);
INSERT INTO tblDay VALUES (2838,1381190400,8,10,2013,1);
INSERT INTO tblDay VALUES (2839,1381276800,9,10,2013,1);
INSERT INTO tblDay VALUES (2840,1381363200,10,10,2013,1);
INSERT INTO tblDay VALUES (2841,1381449600,11,10,2013,1);
INSERT INTO tblDay VALUES (2842,1381536000,12,10,2013,1);
INSERT INTO tblDay VALUES (2843,1381622400,13,10,2013,1);
INSERT INTO tblDay VALUES (2844,1381708800,14,10,2013,1);
INSERT INTO tblDay VALUES (2845,1381795200,15,10,2013,1);
INSERT INTO tblDay VALUES (2846,1381881600,16,10,2013,1);
INSERT INTO tblDay VALUES (2847,1381968000,17,10,2013,1);
INSERT INTO tblDay VALUES (2848,1382054400,18,10,2013,1);
INSERT INTO tblDay VALUES (2849,1382140800,19,10,2013,1);
INSERT INTO tblDay VALUES (2850,1382227200,20,10,2013,1);
INSERT INTO tblDay VALUES (2851,1382313600,21,10,2013,1);
INSERT INTO tblDay VALUES (2852,1382400000,22,10,2013,1);
INSERT INTO tblDay VALUES (2853,1382486400,23,10,2013,1);
INSERT INTO tblDay VALUES (2854,1382572800,24,10,2013,1);
INSERT INTO tblDay VALUES (2855,1382659200,25,10,2013,1);
INSERT INTO tblDay VALUES (2856,1382745600,26,10,2013,1);
INSERT INTO tblDay VALUES (2857,1382832000,27,10,2013,1);
INSERT INTO tblDay VALUES (2858,1382918400,28,10,2013,1);
INSERT INTO tblDay VALUES (2859,1383004800,29,10,2013,1);
INSERT INTO tblDay VALUES (2860,1383091200,30,10,2013,1);
INSERT INTO tblDay VALUES (2861,1383177600,31,10,2013,1);
INSERT INTO tblDay VALUES (2862,1383264000,1,11,2013,1);
INSERT INTO tblDay VALUES (2863,1383350400,2,11,2013,1);
INSERT INTO tblDay VALUES (2864,1383436800,3,11,2013,1);
INSERT INTO tblDay VALUES (2865,1383523200,4,11,2013,1);
INSERT INTO tblDay VALUES (2866,1383609600,5,11,2013,1);
INSERT INTO tblDay VALUES (2867,1383696000,6,11,2013,1);
INSERT INTO tblDay VALUES (2868,1383782400,7,11,2013,1);
INSERT INTO tblDay VALUES (2869,1383868800,8,11,2013,1);
INSERT INTO tblDay VALUES (2870,1383955200,9,11,2013,1);
INSERT INTO tblDay VALUES (2871,1384041600,10,11,2013,1);
INSERT INTO tblDay VALUES (2872,1384128000,11,11,2013,1);
INSERT INTO tblDay VALUES (2873,1384214400,12,11,2013,1);
INSERT INTO tblDay VALUES (2874,1384300800,13,11,2013,1);
INSERT INTO tblDay VALUES (2875,1384387200,14,11,2013,1);
INSERT INTO tblDay VALUES (2876,1384473600,15,11,2013,1);
INSERT INTO tblDay VALUES (2877,1384560000,16,11,2013,1);
INSERT INTO tblDay VALUES (2878,1384646400,17,11,2013,1);
INSERT INTO tblDay VALUES (2879,1384732800,18,11,2013,1);
INSERT INTO tblDay VALUES (2880,1384819200,19,11,2013,1);
INSERT INTO tblDay VALUES (2881,1384905600,20,11,2013,1);
INSERT INTO tblDay VALUES (2882,1384992000,21,11,2013,1);
INSERT INTO tblDay VALUES (2883,1385078400,22,11,2013,1);
INSERT INTO tblDay VALUES (2884,1385164800,23,11,2013,1);
INSERT INTO tblDay VALUES (2885,1385251200,24,11,2013,1);
INSERT INTO tblDay VALUES (2886,1385337600,25,11,2013,1);
INSERT INTO tblDay VALUES (2887,1385424000,26,11,2013,1);
INSERT INTO tblDay VALUES (2888,1385510400,27,11,2013,1);
INSERT INTO tblDay VALUES (2889,1385596800,28,11,2013,1);
INSERT INTO tblDay VALUES (2890,1385683200,29,11,2013,1);
INSERT INTO tblDay VALUES (2891,1385769600,30,11,2013,1);
INSERT INTO tblDay VALUES (2892,1385856000,1,12,2013,1);
INSERT INTO tblDay VALUES (2893,1385942400,2,12,2013,1);
INSERT INTO tblDay VALUES (2894,1386028800,3,12,2013,1);
INSERT INTO tblDay VALUES (2895,1386115200,4,12,2013,1);
INSERT INTO tblDay VALUES (2896,1386201600,5,12,2013,1);
INSERT INTO tblDay VALUES (2897,1386288000,6,12,2013,1);
INSERT INTO tblDay VALUES (2898,1386374400,7,12,2013,1);
INSERT INTO tblDay VALUES (2899,1386460800,8,12,2013,1);
INSERT INTO tblDay VALUES (2900,1386547200,9,12,2013,1);
INSERT INTO tblDay VALUES (2901,1386633600,10,12,2013,1);
INSERT INTO tblDay VALUES (2902,1386720000,11,12,2013,1);
INSERT INTO tblDay VALUES (2903,1386806400,12,12,2013,1);
INSERT INTO tblDay VALUES (2904,1386892800,13,12,2013,1);
INSERT INTO tblDay VALUES (2905,1386979200,14,12,2013,1);
INSERT INTO tblDay VALUES (2906,1387065600,15,12,2013,1);
INSERT INTO tblDay VALUES (2907,1387152000,16,12,2013,1);
INSERT INTO tblDay VALUES (2908,1387238400,17,12,2013,1);
INSERT INTO tblDay VALUES (2909,1387324800,18,12,2013,1);
INSERT INTO tblDay VALUES (2910,1387411200,19,12,2013,1);
INSERT INTO tblDay VALUES (2911,1387497600,20,12,2013,1);
INSERT INTO tblDay VALUES (2912,1387584000,21,12,2013,1);
INSERT INTO tblDay VALUES (2913,1387670400,22,12,2013,1);
INSERT INTO tblDay VALUES (2914,1387756800,23,12,2013,1);
INSERT INTO tblDay VALUES (2915,1387843200,24,12,2013,1);
INSERT INTO tblDay VALUES (2916,1387929600,25,12,2013,1);
INSERT INTO tblDay VALUES (2917,1388016000,26,12,2013,1);
INSERT INTO tblDay VALUES (2918,1388102400,27,12,2013,1);
INSERT INTO tblDay VALUES (2919,1388188800,28,12,2013,1);
INSERT INTO tblDay VALUES (2920,1388275200,29,12,2013,1);
INSERT INTO tblDay VALUES (2921,1388361600,30,12,2013,1);
INSERT INTO tblDay VALUES (2922,1388448000,31,12,2013,1);
INSERT INTO tblDay VALUES (2923,1388534400,1,1,2014,1);
INSERT INTO tblDay VALUES (2924,1388620800,2,1,2014,1);
INSERT INTO tblDay VALUES (2925,1388707200,3,1,2014,1);
INSERT INTO tblDay VALUES (2926,1388793600,4,1,2014,1);
INSERT INTO tblDay VALUES (2927,1388880000,5,1,2014,1);
INSERT INTO tblDay VALUES (2928,1388966400,6,1,2014,1);
INSERT INTO tblDay VALUES (2929,1389052800,7,1,2014,1);
INSERT INTO tblDay VALUES (2930,1389139200,8,1,2014,1);
INSERT INTO tblDay VALUES (2931,1389225600,9,1,2014,1);
INSERT INTO tblDay VALUES (2932,1389312000,10,1,2014,1);
INSERT INTO tblDay VALUES (2933,1389398400,11,1,2014,1);
INSERT INTO tblDay VALUES (2934,1389484800,12,1,2014,1);
INSERT INTO tblDay VALUES (2935,1389571200,13,1,2014,1);
INSERT INTO tblDay VALUES (2936,1389657600,14,1,2014,1);
INSERT INTO tblDay VALUES (2937,1389744000,15,1,2014,1);
INSERT INTO tblDay VALUES (2938,1389830400,16,1,2014,1);
INSERT INTO tblDay VALUES (2939,1389916800,17,1,2014,1);
INSERT INTO tblDay VALUES (2940,1390003200,18,1,2014,1);
INSERT INTO tblDay VALUES (2941,1390089600,19,1,2014,1);
INSERT INTO tblDay VALUES (2942,1390176000,20,1,2014,1);
INSERT INTO tblDay VALUES (2943,1390262400,21,1,2014,1);
INSERT INTO tblDay VALUES (2944,1390348800,22,1,2014,1);
INSERT INTO tblDay VALUES (2945,1390435200,23,1,2014,1);
INSERT INTO tblDay VALUES (2946,1390521600,24,1,2014,1);
INSERT INTO tblDay VALUES (2947,1390608000,25,1,2014,1);
INSERT INTO tblDay VALUES (2948,1390694400,26,1,2014,1);
INSERT INTO tblDay VALUES (2949,1390780800,27,1,2014,1);
INSERT INTO tblDay VALUES (2950,1390867200,28,1,2014,1);
INSERT INTO tblDay VALUES (2951,1390953600,29,1,2014,1);
INSERT INTO tblDay VALUES (2952,1391040000,30,1,2014,1);
INSERT INTO tblDay VALUES (2953,1391126400,31,1,2014,1);
INSERT INTO tblDay VALUES (2954,1391212800,1,2,2014,1);
INSERT INTO tblDay VALUES (2955,1391299200,2,2,2014,1);
INSERT INTO tblDay VALUES (2956,1391385600,3,2,2014,1);
INSERT INTO tblDay VALUES (2957,1391472000,4,2,2014,1);
INSERT INTO tblDay VALUES (2958,1391558400,5,2,2014,1);
INSERT INTO tblDay VALUES (2959,1391644800,6,2,2014,1);
INSERT INTO tblDay VALUES (2960,1391731200,7,2,2014,1);
INSERT INTO tblDay VALUES (2961,1391817600,8,2,2014,1);
INSERT INTO tblDay VALUES (2962,1391904000,9,2,2014,1);
INSERT INTO tblDay VALUES (2963,1391990400,10,2,2014,1);
INSERT INTO tblDay VALUES (2964,1392076800,11,2,2014,1);
INSERT INTO tblDay VALUES (2965,1392163200,12,2,2014,1);
INSERT INTO tblDay VALUES (2966,1392249600,13,2,2014,1);
INSERT INTO tblDay VALUES (2967,1392336000,14,2,2014,1);
INSERT INTO tblDay VALUES (2968,1392422400,15,2,2014,1);
INSERT INTO tblDay VALUES (2969,1392508800,16,2,2014,1);
INSERT INTO tblDay VALUES (2970,1392595200,17,2,2014,1);
INSERT INTO tblDay VALUES (2971,1392681600,18,2,2014,1);
INSERT INTO tblDay VALUES (2972,1392768000,19,2,2014,1);
INSERT INTO tblDay VALUES (2973,1392854400,20,2,2014,1);
INSERT INTO tblDay VALUES (2974,1392940800,21,2,2014,1);
INSERT INTO tblDay VALUES (2975,1393027200,22,2,2014,1);
INSERT INTO tblDay VALUES (2976,1393113600,23,2,2014,1);
INSERT INTO tblDay VALUES (2977,1393200000,24,2,2014,1);
INSERT INTO tblDay VALUES (2978,1393286400,25,2,2014,1);
INSERT INTO tblDay VALUES (2979,1393372800,26,2,2014,1);
INSERT INTO tblDay VALUES (2980,1393459200,27,2,2014,1);
INSERT INTO tblDay VALUES (2981,1393545600,28,2,2014,1);
INSERT INTO tblDay VALUES (2982,1393632000,1,3,2014,1);
INSERT INTO tblDay VALUES (2983,1393718400,2,3,2014,1);
INSERT INTO tblDay VALUES (2984,1393804800,3,3,2014,1);
INSERT INTO tblDay VALUES (2985,1393891200,4,3,2014,1);
INSERT INTO tblDay VALUES (2986,1393977600,5,3,2014,1);
INSERT INTO tblDay VALUES (2987,1394064000,6,3,2014,1);
INSERT INTO tblDay VALUES (2988,1394150400,7,3,2014,1);
INSERT INTO tblDay VALUES (2989,1394236800,8,3,2014,1);
INSERT INTO tblDay VALUES (2990,1394323200,9,3,2014,1);
INSERT INTO tblDay VALUES (2991,1394409600,10,3,2014,1);
INSERT INTO tblDay VALUES (2992,1394496000,11,3,2014,1);
INSERT INTO tblDay VALUES (2993,1394582400,12,3,2014,1);
INSERT INTO tblDay VALUES (2994,1394668800,13,3,2014,1);
INSERT INTO tblDay VALUES (2995,1394755200,14,3,2014,1);
INSERT INTO tblDay VALUES (2996,1394841600,15,3,2014,1);
INSERT INTO tblDay VALUES (2997,1394928000,16,3,2014,1);
INSERT INTO tblDay VALUES (2998,1395014400,17,3,2014,1);
INSERT INTO tblDay VALUES (2999,1395100800,18,3,2014,1);
INSERT INTO tblDay VALUES (3000,1395187200,19,3,2014,1);
INSERT INTO tblDay VALUES (3001,1395273600,20,3,2014,1);
INSERT INTO tblDay VALUES (3002,1395360000,21,3,2014,1);
INSERT INTO tblDay VALUES (3003,1395446400,22,3,2014,1);
INSERT INTO tblDay VALUES (3004,1395532800,23,3,2014,1);
INSERT INTO tblDay VALUES (3005,1395619200,24,3,2014,1);
INSERT INTO tblDay VALUES (3006,1395705600,25,3,2014,1);
INSERT INTO tblDay VALUES (3007,1395792000,26,3,2014,1);
INSERT INTO tblDay VALUES (3008,1395878400,27,3,2014,1);
INSERT INTO tblDay VALUES (3009,1395964800,28,3,2014,1);
INSERT INTO tblDay VALUES (3010,1396051200,29,3,2014,1);
INSERT INTO tblDay VALUES (3011,1396137600,30,3,2014,1);
INSERT INTO tblDay VALUES (3012,1396224000,31,3,2014,1);
INSERT INTO tblDay VALUES (3013,1396310400,1,4,2014,1);
INSERT INTO tblDay VALUES (3014,1396396800,2,4,2014,1);
INSERT INTO tblDay VALUES (3015,1396483200,3,4,2014,1);
INSERT INTO tblDay VALUES (3016,1396569600,4,4,2014,1);
INSERT INTO tblDay VALUES (3017,1396656000,5,4,2014,1);
INSERT INTO tblDay VALUES (3018,1396742400,6,4,2014,1);
INSERT INTO tblDay VALUES (3019,1396828800,7,4,2014,1);
INSERT INTO tblDay VALUES (3020,1396915200,8,4,2014,1);
INSERT INTO tblDay VALUES (3021,1397001600,9,4,2014,1);
INSERT INTO tblDay VALUES (3022,1397088000,10,4,2014,1);
INSERT INTO tblDay VALUES (3023,1397174400,11,4,2014,1);
INSERT INTO tblDay VALUES (3024,1397260800,12,4,2014,1);
INSERT INTO tblDay VALUES (3025,1397347200,13,4,2014,1);
INSERT INTO tblDay VALUES (3026,1397433600,14,4,2014,1);
INSERT INTO tblDay VALUES (3027,1397520000,15,4,2014,1);
INSERT INTO tblDay VALUES (3028,1397606400,16,4,2014,1);
INSERT INTO tblDay VALUES (3029,1397692800,17,4,2014,1);
INSERT INTO tblDay VALUES (3030,1397779200,18,4,2014,1);
INSERT INTO tblDay VALUES (3031,1397865600,19,4,2014,1);
INSERT INTO tblDay VALUES (3032,1397952000,20,4,2014,1);
INSERT INTO tblDay VALUES (3033,1398038400,21,4,2014,1);
INSERT INTO tblDay VALUES (3034,1398124800,22,4,2014,1);
INSERT INTO tblDay VALUES (3035,1398211200,23,4,2014,1);
INSERT INTO tblDay VALUES (3036,1398297600,24,4,2014,1);
INSERT INTO tblDay VALUES (3037,1398384000,25,4,2014,1);
INSERT INTO tblDay VALUES (3038,1398470400,26,4,2014,1);
INSERT INTO tblDay VALUES (3039,1398556800,27,4,2014,1);
INSERT INTO tblDay VALUES (3040,1398643200,28,4,2014,1);
INSERT INTO tblDay VALUES (3041,1398729600,29,4,2014,1);
INSERT INTO tblDay VALUES (3042,1398816000,30,4,2014,1);
INSERT INTO tblDay VALUES (3043,1398902400,1,5,2014,1);
INSERT INTO tblDay VALUES (3044,1398988800,2,5,2014,1);
INSERT INTO tblDay VALUES (3045,1399075200,3,5,2014,1);
INSERT INTO tblDay VALUES (3046,1399161600,4,5,2014,1);
INSERT INTO tblDay VALUES (3047,1399248000,5,5,2014,1);
INSERT INTO tblDay VALUES (3048,1399334400,6,5,2014,1);
INSERT INTO tblDay VALUES (3049,1399420800,7,5,2014,1);
INSERT INTO tblDay VALUES (3050,1399507200,8,5,2014,1);
INSERT INTO tblDay VALUES (3051,1399593600,9,5,2014,1);
INSERT INTO tblDay VALUES (3052,1399680000,10,5,2014,1);
INSERT INTO tblDay VALUES (3053,1399766400,11,5,2014,1);
INSERT INTO tblDay VALUES (3054,1399852800,12,5,2014,1);
INSERT INTO tblDay VALUES (3055,1399939200,13,5,2014,1);
INSERT INTO tblDay VALUES (3056,1400025600,14,5,2014,1);
INSERT INTO tblDay VALUES (3057,1400112000,15,5,2014,1);
INSERT INTO tblDay VALUES (3058,1400198400,16,5,2014,1);
INSERT INTO tblDay VALUES (3059,1400284800,17,5,2014,1);
INSERT INTO tblDay VALUES (3060,1400371200,18,5,2014,1);
INSERT INTO tblDay VALUES (3061,1400457600,19,5,2014,1);
INSERT INTO tblDay VALUES (3062,1400544000,20,5,2014,1);
INSERT INTO tblDay VALUES (3063,1400630400,21,5,2014,1);
INSERT INTO tblDay VALUES (3064,1400716800,22,5,2014,1);
INSERT INTO tblDay VALUES (3065,1400803200,23,5,2014,1);
INSERT INTO tblDay VALUES (3066,1400889600,24,5,2014,1);
INSERT INTO tblDay VALUES (3067,1400976000,25,5,2014,1);
INSERT INTO tblDay VALUES (3068,1401062400,26,5,2014,1);
INSERT INTO tblDay VALUES (3069,1401148800,27,5,2014,1);
INSERT INTO tblDay VALUES (3070,1401235200,28,5,2014,1);
INSERT INTO tblDay VALUES (3071,1401321600,29,5,2014,1);
INSERT INTO tblDay VALUES (3072,1401408000,30,5,2014,1);
INSERT INTO tblDay VALUES (3073,1401494400,31,5,2014,1);
INSERT INTO tblDay VALUES (3074,1401580800,1,6,2014,1);
INSERT INTO tblDay VALUES (3075,1401667200,2,6,2014,1);
INSERT INTO tblDay VALUES (3076,1401753600,3,6,2014,1);
INSERT INTO tblDay VALUES (3077,1401840000,4,6,2014,1);
INSERT INTO tblDay VALUES (3078,1401926400,5,6,2014,1);
INSERT INTO tblDay VALUES (3079,1402012800,6,6,2014,1);
INSERT INTO tblDay VALUES (3080,1402099200,7,6,2014,1);
INSERT INTO tblDay VALUES (3081,1402185600,8,6,2014,1);
INSERT INTO tblDay VALUES (3082,1402272000,9,6,2014,1);
INSERT INTO tblDay VALUES (3083,1402358400,10,6,2014,1);
INSERT INTO tblDay VALUES (3084,1402444800,11,6,2014,1);
INSERT INTO tblDay VALUES (3085,1402531200,12,6,2014,1);
INSERT INTO tblDay VALUES (3086,1402617600,13,6,2014,1);
INSERT INTO tblDay VALUES (3087,1402704000,14,6,2014,1);
INSERT INTO tblDay VALUES (3088,1402790400,15,6,2014,1);
INSERT INTO tblDay VALUES (3089,1402876800,16,6,2014,1);
INSERT INTO tblDay VALUES (3090,1402963200,17,6,2014,1);
INSERT INTO tblDay VALUES (3091,1403049600,18,6,2014,1);
INSERT INTO tblDay VALUES (3092,1403136000,19,6,2014,1);
INSERT INTO tblDay VALUES (3093,1403222400,20,6,2014,1);
INSERT INTO tblDay VALUES (3094,1403308800,21,6,2014,1);
INSERT INTO tblDay VALUES (3095,1403395200,22,6,2014,1);
INSERT INTO tblDay VALUES (3096,1403481600,23,6,2014,1);
INSERT INTO tblDay VALUES (3097,1403568000,24,6,2014,1);
INSERT INTO tblDay VALUES (3098,1403654400,25,6,2014,1);
INSERT INTO tblDay VALUES (3099,1403740800,26,6,2014,1);
INSERT INTO tblDay VALUES (3100,1403827200,27,6,2014,1);
INSERT INTO tblDay VALUES (3101,1403913600,28,6,2014,1);
INSERT INTO tblDay VALUES (3102,1404000000,29,6,2014,1);
INSERT INTO tblDay VALUES (3103,1404086400,30,6,2014,1);
INSERT INTO tblDay VALUES (3104,1404172800,1,7,2014,1);
INSERT INTO tblDay VALUES (3105,1404259200,2,7,2014,1);
INSERT INTO tblDay VALUES (3106,1404345600,3,7,2014,1);
INSERT INTO tblDay VALUES (3107,1404432000,4,7,2014,1);
INSERT INTO tblDay VALUES (3108,1404518400,5,7,2014,1);
INSERT INTO tblDay VALUES (3109,1404604800,6,7,2014,1);
INSERT INTO tblDay VALUES (3110,1404691200,7,7,2014,1);
INSERT INTO tblDay VALUES (3111,1404777600,8,7,2014,1);
INSERT INTO tblDay VALUES (3112,1404864000,9,7,2014,1);
INSERT INTO tblDay VALUES (3113,1404950400,10,7,2014,1);
INSERT INTO tblDay VALUES (3114,1405036800,11,7,2014,1);
INSERT INTO tblDay VALUES (3115,1405123200,12,7,2014,1);
INSERT INTO tblDay VALUES (3116,1405209600,13,7,2014,1);
INSERT INTO tblDay VALUES (3117,1405296000,14,7,2014,1);
INSERT INTO tblDay VALUES (3118,1405382400,15,7,2014,1);
INSERT INTO tblDay VALUES (3119,1405468800,16,7,2014,1);
INSERT INTO tblDay VALUES (3120,1405555200,17,7,2014,1);
INSERT INTO tblDay VALUES (3121,1405641600,18,7,2014,1);
INSERT INTO tblDay VALUES (3122,1405728000,19,7,2014,1);
INSERT INTO tblDay VALUES (3123,1405814400,20,7,2014,1);
INSERT INTO tblDay VALUES (3124,1405900800,21,7,2014,1);
INSERT INTO tblDay VALUES (3125,1405987200,22,7,2014,1);
INSERT INTO tblDay VALUES (3126,1406073600,23,7,2014,1);
INSERT INTO tblDay VALUES (3127,1406160000,24,7,2014,1);
INSERT INTO tblDay VALUES (3128,1406246400,25,7,2014,1);
INSERT INTO tblDay VALUES (3129,1406332800,26,7,2014,1);
INSERT INTO tblDay VALUES (3130,1406419200,27,7,2014,1);
INSERT INTO tblDay VALUES (3131,1406505600,28,7,2014,1);
INSERT INTO tblDay VALUES (3132,1406592000,29,7,2014,1);
INSERT INTO tblDay VALUES (3133,1406678400,30,7,2014,1);
INSERT INTO tblDay VALUES (3134,1406764800,31,7,2014,1);
INSERT INTO tblDay VALUES (3135,1406851200,1,8,2014,1);
INSERT INTO tblDay VALUES (3136,1406937600,2,8,2014,1);
INSERT INTO tblDay VALUES (3137,1407024000,3,8,2014,1);
INSERT INTO tblDay VALUES (3138,1407110400,4,8,2014,1);
INSERT INTO tblDay VALUES (3139,1407196800,5,8,2014,1);
INSERT INTO tblDay VALUES (3140,1407283200,6,8,2014,1);
INSERT INTO tblDay VALUES (3141,1407369600,7,8,2014,1);
INSERT INTO tblDay VALUES (3142,1407456000,8,8,2014,1);
INSERT INTO tblDay VALUES (3143,1407542400,9,8,2014,1);
INSERT INTO tblDay VALUES (3144,1407628800,10,8,2014,1);
INSERT INTO tblDay VALUES (3145,1407715200,11,8,2014,1);
INSERT INTO tblDay VALUES (3146,1407801600,12,8,2014,1);
INSERT INTO tblDay VALUES (3147,1407888000,13,8,2014,1);
INSERT INTO tblDay VALUES (3148,1407974400,14,8,2014,1);
INSERT INTO tblDay VALUES (3149,1408060800,15,8,2014,1);
INSERT INTO tblDay VALUES (3150,1408147200,16,8,2014,1);
INSERT INTO tblDay VALUES (3151,1408233600,17,8,2014,1);
INSERT INTO tblDay VALUES (3152,1408320000,18,8,2014,1);
INSERT INTO tblDay VALUES (3153,1408406400,19,8,2014,1);
INSERT INTO tblDay VALUES (3154,1408492800,20,8,2014,1);
INSERT INTO tblDay VALUES (3155,1408579200,21,8,2014,1);
INSERT INTO tblDay VALUES (3156,1408665600,22,8,2014,1);
INSERT INTO tblDay VALUES (3157,1408752000,23,8,2014,1);
INSERT INTO tblDay VALUES (3158,1408838400,24,8,2014,1);
INSERT INTO tblDay VALUES (3159,1408924800,25,8,2014,1);
INSERT INTO tblDay VALUES (3160,1409011200,26,8,2014,1);
INSERT INTO tblDay VALUES (3161,1409097600,27,8,2014,1);
INSERT INTO tblDay VALUES (3162,1409184000,28,8,2014,1);
INSERT INTO tblDay VALUES (3163,1409270400,29,8,2014,1);
INSERT INTO tblDay VALUES (3164,1409356800,30,8,2014,1);
INSERT INTO tblDay VALUES (3165,1409443200,31,8,2014,1);
INSERT INTO tblDay VALUES (3166,1409529600,1,9,2014,1);
INSERT INTO tblDay VALUES (3167,1409616000,2,9,2014,1);
INSERT INTO tblDay VALUES (3168,1409702400,3,9,2014,1);
INSERT INTO tblDay VALUES (3169,1409788800,4,9,2014,1);
INSERT INTO tblDay VALUES (3170,1409875200,5,9,2014,1);
INSERT INTO tblDay VALUES (3171,1409961600,6,9,2014,1);
INSERT INTO tblDay VALUES (3172,1410048000,7,9,2014,1);
INSERT INTO tblDay VALUES (3173,1410134400,8,9,2014,1);
INSERT INTO tblDay VALUES (3174,1410220800,9,9,2014,1);
INSERT INTO tblDay VALUES (3175,1410307200,10,9,2014,1);
INSERT INTO tblDay VALUES (3176,1410393600,11,9,2014,1);
INSERT INTO tblDay VALUES (3177,1410480000,12,9,2014,1);
INSERT INTO tblDay VALUES (3178,1410566400,13,9,2014,1);
INSERT INTO tblDay VALUES (3179,1410652800,14,9,2014,1);
INSERT INTO tblDay VALUES (3180,1410739200,15,9,2014,1);
INSERT INTO tblDay VALUES (3181,1410825600,16,9,2014,1);
INSERT INTO tblDay VALUES (3182,1410912000,17,9,2014,1);
INSERT INTO tblDay VALUES (3183,1410998400,18,9,2014,1);
INSERT INTO tblDay VALUES (3184,1411084800,19,9,2014,1);
INSERT INTO tblDay VALUES (3185,1411171200,20,9,2014,1);
INSERT INTO tblDay VALUES (3186,1411257600,21,9,2014,1);
INSERT INTO tblDay VALUES (3187,1411344000,22,9,2014,1);
INSERT INTO tblDay VALUES (3188,1411430400,23,9,2014,1);
INSERT INTO tblDay VALUES (3189,1411516800,24,9,2014,1);
INSERT INTO tblDay VALUES (3190,1411603200,25,9,2014,1);
INSERT INTO tblDay VALUES (3191,1411689600,26,9,2014,1);
INSERT INTO tblDay VALUES (3192,1411776000,27,9,2014,1);
INSERT INTO tblDay VALUES (3193,1411862400,28,9,2014,1);
INSERT INTO tblDay VALUES (3194,1411948800,29,9,2014,1);
INSERT INTO tblDay VALUES (3195,1412035200,30,9,2014,1);
INSERT INTO tblDay VALUES (3196,1412121600,1,10,2014,1);
INSERT INTO tblDay VALUES (3197,1412208000,2,10,2014,1);
INSERT INTO tblDay VALUES (3198,1412294400,3,10,2014,1);
INSERT INTO tblDay VALUES (3199,1412380800,4,10,2014,1);
INSERT INTO tblDay VALUES (3200,1412467200,5,10,2014,1);
INSERT INTO tblDay VALUES (3201,1412553600,6,10,2014,1);
INSERT INTO tblDay VALUES (3202,1412640000,7,10,2014,1);
INSERT INTO tblDay VALUES (3203,1412726400,8,10,2014,1);
INSERT INTO tblDay VALUES (3204,1412812800,9,10,2014,1);
INSERT INTO tblDay VALUES (3205,1412899200,10,10,2014,1);
INSERT INTO tblDay VALUES (3206,1412985600,11,10,2014,1);
INSERT INTO tblDay VALUES (3207,1413072000,12,10,2014,1);
INSERT INTO tblDay VALUES (3208,1413158400,13,10,2014,1);
INSERT INTO tblDay VALUES (3209,1413244800,14,10,2014,1);
INSERT INTO tblDay VALUES (3210,1413331200,15,10,2014,1);
INSERT INTO tblDay VALUES (3211,1413417600,16,10,2014,1);
INSERT INTO tblDay VALUES (3212,1413504000,17,10,2014,1);
INSERT INTO tblDay VALUES (3213,1413590400,18,10,2014,1);
INSERT INTO tblDay VALUES (3214,1413676800,19,10,2014,1);
INSERT INTO tblDay VALUES (3215,1413763200,20,10,2014,1);
INSERT INTO tblDay VALUES (3216,1413849600,21,10,2014,1);
INSERT INTO tblDay VALUES (3217,1413936000,22,10,2014,1);
INSERT INTO tblDay VALUES (3218,1414022400,23,10,2014,1);
INSERT INTO tblDay VALUES (3219,1414108800,24,10,2014,1);
INSERT INTO tblDay VALUES (3220,1414195200,25,10,2014,1);
INSERT INTO tblDay VALUES (3221,1414281600,26,10,2014,1);
INSERT INTO tblDay VALUES (3222,1414368000,27,10,2014,1);
INSERT INTO tblDay VALUES (3223,1414454400,28,10,2014,1);
INSERT INTO tblDay VALUES (3224,1414540800,29,10,2014,1);
INSERT INTO tblDay VALUES (3225,1414627200,30,10,2014,1);
INSERT INTO tblDay VALUES (3226,1414713600,31,10,2014,1);
INSERT INTO tblDay VALUES (3227,1414800000,1,11,2014,1);
INSERT INTO tblDay VALUES (3228,1414886400,2,11,2014,1);
INSERT INTO tblDay VALUES (3229,1414972800,3,11,2014,1);
INSERT INTO tblDay VALUES (3230,1415059200,4,11,2014,1);
INSERT INTO tblDay VALUES (3231,1415145600,5,11,2014,1);
INSERT INTO tblDay VALUES (3232,1415232000,6,11,2014,1);
INSERT INTO tblDay VALUES (3233,1415318400,7,11,2014,1);
INSERT INTO tblDay VALUES (3234,1415404800,8,11,2014,1);
INSERT INTO tblDay VALUES (3235,1415491200,9,11,2014,1);
INSERT INTO tblDay VALUES (3236,1415577600,10,11,2014,1);
INSERT INTO tblDay VALUES (3237,1415664000,11,11,2014,1);
INSERT INTO tblDay VALUES (3238,1415750400,12,11,2014,1);
INSERT INTO tblDay VALUES (3239,1415836800,13,11,2014,1);
INSERT INTO tblDay VALUES (3240,1415923200,14,11,2014,1);
INSERT INTO tblDay VALUES (3241,1416009600,15,11,2014,1);
INSERT INTO tblDay VALUES (3242,1416096000,16,11,2014,1);
INSERT INTO tblDay VALUES (3243,1416182400,17,11,2014,1);
INSERT INTO tblDay VALUES (3244,1416268800,18,11,2014,1);
INSERT INTO tblDay VALUES (3245,1416355200,19,11,2014,1);
INSERT INTO tblDay VALUES (3246,1416441600,20,11,2014,1);
INSERT INTO tblDay VALUES (3247,1416528000,21,11,2014,1);
INSERT INTO tblDay VALUES (3248,1416614400,22,11,2014,1);
INSERT INTO tblDay VALUES (3249,1416700800,23,11,2014,1);
INSERT INTO tblDay VALUES (3250,1416787200,24,11,2014,1);
INSERT INTO tblDay VALUES (3251,1416873600,25,11,2014,1);
INSERT INTO tblDay VALUES (3252,1416960000,26,11,2014,1);
INSERT INTO tblDay VALUES (3253,1417046400,27,11,2014,1);
INSERT INTO tblDay VALUES (3254,1417132800,28,11,2014,1);
INSERT INTO tblDay VALUES (3255,1417219200,29,11,2014,1);
INSERT INTO tblDay VALUES (3256,1417305600,30,11,2014,1);
INSERT INTO tblDay VALUES (3257,1417392000,1,12,2014,1);
INSERT INTO tblDay VALUES (3258,1417478400,2,12,2014,1);
INSERT INTO tblDay VALUES (3259,1417564800,3,12,2014,1);
INSERT INTO tblDay VALUES (3260,1417651200,4,12,2014,1);
INSERT INTO tblDay VALUES (3261,1417737600,5,12,2014,1);
INSERT INTO tblDay VALUES (3262,1417824000,6,12,2014,1);
INSERT INTO tblDay VALUES (3263,1417910400,7,12,2014,1);
INSERT INTO tblDay VALUES (3264,1417996800,8,12,2014,1);
INSERT INTO tblDay VALUES (3265,1418083200,9,12,2014,1);
INSERT INTO tblDay VALUES (3266,1418169600,10,12,2014,1);
INSERT INTO tblDay VALUES (3267,1418256000,11,12,2014,1);
INSERT INTO tblDay VALUES (3268,1418342400,12,12,2014,1);
INSERT INTO tblDay VALUES (3269,1418428800,13,12,2014,1);
INSERT INTO tblDay VALUES (3270,1418515200,14,12,2014,1);
INSERT INTO tblDay VALUES (3271,1418601600,15,12,2014,1);
INSERT INTO tblDay VALUES (3272,1418688000,16,12,2014,1);
INSERT INTO tblDay VALUES (3273,1418774400,17,12,2014,1);
INSERT INTO tblDay VALUES (3274,1418860800,18,12,2014,1);
INSERT INTO tblDay VALUES (3275,1418947200,19,12,2014,1);
INSERT INTO tblDay VALUES (3276,1419033600,20,12,2014,1);
INSERT INTO tblDay VALUES (3277,1419120000,21,12,2014,1);
INSERT INTO tblDay VALUES (3278,1419206400,22,12,2014,1);
INSERT INTO tblDay VALUES (3279,1419292800,23,12,2014,1);
INSERT INTO tblDay VALUES (3280,1419379200,24,12,2014,1);
INSERT INTO tblDay VALUES (3281,1419465600,25,12,2014,1);
INSERT INTO tblDay VALUES (3282,1419552000,26,12,2014,1);
INSERT INTO tblDay VALUES (3283,1419638400,27,12,2014,1);
INSERT INTO tblDay VALUES (3284,1419724800,28,12,2014,1);
INSERT INTO tblDay VALUES (3285,1419811200,29,12,2014,1);
INSERT INTO tblDay VALUES (3286,1419897600,30,12,2014,1);
INSERT INTO tblDay VALUES (3287,1419984000,31,12,2014,1);
INSERT INTO tblDay VALUES (3288,1420070400,1,1,2015,1);
INSERT INTO tblDay VALUES (3289,1420156800,2,1,2015,1);
INSERT INTO tblDay VALUES (3290,1420243200,3,1,2015,1);
INSERT INTO tblDay VALUES (3291,1420329600,4,1,2015,1);
INSERT INTO tblDay VALUES (3292,1420416000,5,1,2015,1);
INSERT INTO tblDay VALUES (3293,1420502400,6,1,2015,1);
INSERT INTO tblDay VALUES (3294,1420588800,7,1,2015,1);
INSERT INTO tblDay VALUES (3295,1420675200,8,1,2015,1);
INSERT INTO tblDay VALUES (3296,1420761600,9,1,2015,1);
INSERT INTO tblDay VALUES (3297,1420848000,10,1,2015,1);
INSERT INTO tblDay VALUES (3298,1420934400,11,1,2015,1);
INSERT INTO tblDay VALUES (3299,1421020800,12,1,2015,1);
INSERT INTO tblDay VALUES (3300,1421107200,13,1,2015,1);
INSERT INTO tblDay VALUES (3301,1421193600,14,1,2015,1);
INSERT INTO tblDay VALUES (3302,1421280000,15,1,2015,1);
INSERT INTO tblDay VALUES (3303,1421366400,16,1,2015,1);
INSERT INTO tblDay VALUES (3304,1421452800,17,1,2015,1);
INSERT INTO tblDay VALUES (3305,1421539200,18,1,2015,1);
INSERT INTO tblDay VALUES (3306,1421625600,19,1,2015,1);
INSERT INTO tblDay VALUES (3307,1421712000,20,1,2015,1);
INSERT INTO tblDay VALUES (3308,1421798400,21,1,2015,1);
INSERT INTO tblDay VALUES (3309,1421884800,22,1,2015,1);
INSERT INTO tblDay VALUES (3310,1421971200,23,1,2015,1);
INSERT INTO tblDay VALUES (3311,1422057600,24,1,2015,1);
INSERT INTO tblDay VALUES (3312,1422144000,25,1,2015,1);
INSERT INTO tblDay VALUES (3313,1422230400,26,1,2015,1);
INSERT INTO tblDay VALUES (3314,1422316800,27,1,2015,1);
INSERT INTO tblDay VALUES (3315,1422403200,28,1,2015,1);
INSERT INTO tblDay VALUES (3316,1422489600,29,1,2015,1);
INSERT INTO tblDay VALUES (3317,1422576000,30,1,2015,1);
INSERT INTO tblDay VALUES (3318,1422662400,31,1,2015,1);
INSERT INTO tblDay VALUES (3319,1422748800,1,2,2015,1);
INSERT INTO tblDay VALUES (3320,1422835200,2,2,2015,1);
INSERT INTO tblDay VALUES (3321,1422921600,3,2,2015,1);
INSERT INTO tblDay VALUES (3322,1423008000,4,2,2015,1);
INSERT INTO tblDay VALUES (3323,1423094400,5,2,2015,1);
INSERT INTO tblDay VALUES (3324,1423180800,6,2,2015,1);
INSERT INTO tblDay VALUES (3325,1423267200,7,2,2015,1);
INSERT INTO tblDay VALUES (3326,1423353600,8,2,2015,1);
INSERT INTO tblDay VALUES (3327,1423440000,9,2,2015,1);
INSERT INTO tblDay VALUES (3328,1423526400,10,2,2015,1);
INSERT INTO tblDay VALUES (3329,1423612800,11,2,2015,1);
INSERT INTO tblDay VALUES (3330,1423699200,12,2,2015,1);
INSERT INTO tblDay VALUES (3331,1423785600,13,2,2015,1);
INSERT INTO tblDay VALUES (3332,1423872000,14,2,2015,1);
INSERT INTO tblDay VALUES (3333,1423958400,15,2,2015,1);
INSERT INTO tblDay VALUES (3334,1424044800,16,2,2015,1);
INSERT INTO tblDay VALUES (3335,1424131200,17,2,2015,1);
INSERT INTO tblDay VALUES (3336,1424217600,18,2,2015,1);
INSERT INTO tblDay VALUES (3337,1424304000,19,2,2015,1);
INSERT INTO tblDay VALUES (3338,1424390400,20,2,2015,1);
INSERT INTO tblDay VALUES (3339,1424476800,21,2,2015,1);
INSERT INTO tblDay VALUES (3340,1424563200,22,2,2015,1);
INSERT INTO tblDay VALUES (3341,1424649600,23,2,2015,1);
INSERT INTO tblDay VALUES (3342,1424736000,24,2,2015,1);
INSERT INTO tblDay VALUES (3343,1424822400,25,2,2015,1);
INSERT INTO tblDay VALUES (3344,1424908800,26,2,2015,1);
INSERT INTO tblDay VALUES (3345,1424995200,27,2,2015,1);
INSERT INTO tblDay VALUES (3346,1425081600,28,2,2015,1);
INSERT INTO tblDay VALUES (3347,1425168000,1,3,2015,1);
INSERT INTO tblDay VALUES (3348,1425254400,2,3,2015,1);
INSERT INTO tblDay VALUES (3349,1425340800,3,3,2015,1);
INSERT INTO tblDay VALUES (3350,1425427200,4,3,2015,1);
INSERT INTO tblDay VALUES (3351,1425513600,5,3,2015,1);
INSERT INTO tblDay VALUES (3352,1425600000,6,3,2015,1);
INSERT INTO tblDay VALUES (3353,1425686400,7,3,2015,1);
INSERT INTO tblDay VALUES (3354,1425772800,8,3,2015,1);
INSERT INTO tblDay VALUES (3355,1425859200,9,3,2015,1);
INSERT INTO tblDay VALUES (3356,1425945600,10,3,2015,1);
INSERT INTO tblDay VALUES (3357,1426032000,11,3,2015,1);
INSERT INTO tblDay VALUES (3358,1426118400,12,3,2015,1);
INSERT INTO tblDay VALUES (3359,1426204800,13,3,2015,1);
INSERT INTO tblDay VALUES (3360,1426291200,14,3,2015,1);
INSERT INTO tblDay VALUES (3361,1426377600,15,3,2015,1);
INSERT INTO tblDay VALUES (3362,1426464000,16,3,2015,1);
INSERT INTO tblDay VALUES (3363,1426550400,17,3,2015,1);
INSERT INTO tblDay VALUES (3364,1426636800,18,3,2015,1);
INSERT INTO tblDay VALUES (3365,1426723200,19,3,2015,1);
INSERT INTO tblDay VALUES (3366,1426809600,20,3,2015,1);
INSERT INTO tblDay VALUES (3367,1426896000,21,3,2015,1);
INSERT INTO tblDay VALUES (3368,1426982400,22,3,2015,1);
INSERT INTO tblDay VALUES (3369,1427068800,23,3,2015,1);
INSERT INTO tblDay VALUES (3370,1427155200,24,3,2015,1);
INSERT INTO tblDay VALUES (3371,1427241600,25,3,2015,1);
INSERT INTO tblDay VALUES (3372,1427328000,26,3,2015,1);
INSERT INTO tblDay VALUES (3373,1427414400,27,3,2015,1);
INSERT INTO tblDay VALUES (3374,1427500800,28,3,2015,1);
INSERT INTO tblDay VALUES (3375,1427587200,29,3,2015,1);
INSERT INTO tblDay VALUES (3376,1427673600,30,3,2015,1);
INSERT INTO tblDay VALUES (3377,1427760000,31,3,2015,1);
INSERT INTO tblDay VALUES (3378,1427846400,1,4,2015,1);
INSERT INTO tblDay VALUES (3379,1427932800,2,4,2015,1);
INSERT INTO tblDay VALUES (3380,1428019200,3,4,2015,1);
INSERT INTO tblDay VALUES (3381,1428105600,4,4,2015,1);
INSERT INTO tblDay VALUES (3382,1428192000,5,4,2015,1);
INSERT INTO tblDay VALUES (3383,1428278400,6,4,2015,1);
INSERT INTO tblDay VALUES (3384,1428364800,7,4,2015,1);
INSERT INTO tblDay VALUES (3385,1428451200,8,4,2015,1);
INSERT INTO tblDay VALUES (3386,1428537600,9,4,2015,1);
INSERT INTO tblDay VALUES (3387,1428624000,10,4,2015,1);
INSERT INTO tblDay VALUES (3388,1428710400,11,4,2015,1);
INSERT INTO tblDay VALUES (3389,1428796800,12,4,2015,1);
INSERT INTO tblDay VALUES (3390,1428883200,13,4,2015,1);
INSERT INTO tblDay VALUES (3391,1428969600,14,4,2015,1);
INSERT INTO tblDay VALUES (3392,1429056000,15,4,2015,1);
INSERT INTO tblDay VALUES (3393,1429142400,16,4,2015,1);
INSERT INTO tblDay VALUES (3394,1429228800,17,4,2015,1);
INSERT INTO tblDay VALUES (3395,1429315200,18,4,2015,1);
INSERT INTO tblDay VALUES (3396,1429401600,19,4,2015,1);
INSERT INTO tblDay VALUES (3397,1429488000,20,4,2015,1);
INSERT INTO tblDay VALUES (3398,1429574400,21,4,2015,1);
INSERT INTO tblDay VALUES (3399,1429660800,22,4,2015,1);
INSERT INTO tblDay VALUES (3400,1429747200,23,4,2015,1);
INSERT INTO tblDay VALUES (3401,1429833600,24,4,2015,1);
INSERT INTO tblDay VALUES (3402,1429920000,25,4,2015,1);
INSERT INTO tblDay VALUES (3403,1430006400,26,4,2015,1);
INSERT INTO tblDay VALUES (3404,1430092800,27,4,2015,1);
INSERT INTO tblDay VALUES (3405,1430179200,28,4,2015,1);
INSERT INTO tblDay VALUES (3406,1430265600,29,4,2015,1);
INSERT INTO tblDay VALUES (3407,1430352000,30,4,2015,1);
INSERT INTO tblDay VALUES (3408,1430438400,1,5,2015,1);
INSERT INTO tblDay VALUES (3409,1430524800,2,5,2015,1);
INSERT INTO tblDay VALUES (3410,1430611200,3,5,2015,1);
INSERT INTO tblDay VALUES (3411,1430697600,4,5,2015,1);
INSERT INTO tblDay VALUES (3412,1430784000,5,5,2015,1);
INSERT INTO tblDay VALUES (3413,1430870400,6,5,2015,1);
INSERT INTO tblDay VALUES (3414,1430956800,7,5,2015,1);
INSERT INTO tblDay VALUES (3415,1431043200,8,5,2015,1);
INSERT INTO tblDay VALUES (3416,1431129600,9,5,2015,1);
INSERT INTO tblDay VALUES (3417,1431216000,10,5,2015,1);
INSERT INTO tblDay VALUES (3418,1431302400,11,5,2015,1);
INSERT INTO tblDay VALUES (3419,1431388800,12,5,2015,1);
INSERT INTO tblDay VALUES (3420,1431475200,13,5,2015,1);
INSERT INTO tblDay VALUES (3421,1431561600,14,5,2015,1);
INSERT INTO tblDay VALUES (3422,1431648000,15,5,2015,1);
INSERT INTO tblDay VALUES (3423,1431734400,16,5,2015,1);
INSERT INTO tblDay VALUES (3424,1431820800,17,5,2015,1);
INSERT INTO tblDay VALUES (3425,1431907200,18,5,2015,1);
INSERT INTO tblDay VALUES (3426,1431993600,19,5,2015,1);
INSERT INTO tblDay VALUES (3427,1432080000,20,5,2015,1);
INSERT INTO tblDay VALUES (3428,1432166400,21,5,2015,1);
INSERT INTO tblDay VALUES (3429,1432252800,22,5,2015,1);
INSERT INTO tblDay VALUES (3430,1432339200,23,5,2015,1);
INSERT INTO tblDay VALUES (3431,1432425600,24,5,2015,1);
INSERT INTO tblDay VALUES (3432,1432512000,25,5,2015,1);
INSERT INTO tblDay VALUES (3433,1432598400,26,5,2015,1);
INSERT INTO tblDay VALUES (3434,1432684800,27,5,2015,1);
INSERT INTO tblDay VALUES (3435,1432771200,28,5,2015,1);
INSERT INTO tblDay VALUES (3436,1432857600,29,5,2015,1);
INSERT INTO tblDay VALUES (3437,1432944000,30,5,2015,1);
INSERT INTO tblDay VALUES (3438,1433030400,31,5,2015,1);
INSERT INTO tblDay VALUES (3439,1433116800,1,6,2015,1);
INSERT INTO tblDay VALUES (3440,1433203200,2,6,2015,1);
INSERT INTO tblDay VALUES (3441,1433289600,3,6,2015,1);
INSERT INTO tblDay VALUES (3442,1433376000,4,6,2015,1);
INSERT INTO tblDay VALUES (3443,1433462400,5,6,2015,1);
INSERT INTO tblDay VALUES (3444,1433548800,6,6,2015,1);
INSERT INTO tblDay VALUES (3445,1433635200,7,6,2015,1);
INSERT INTO tblDay VALUES (3446,1433721600,8,6,2015,1);
INSERT INTO tblDay VALUES (3447,1433808000,9,6,2015,1);
INSERT INTO tblDay VALUES (3448,1433894400,10,6,2015,1);
INSERT INTO tblDay VALUES (3449,1433980800,11,6,2015,1);
INSERT INTO tblDay VALUES (3450,1434067200,12,6,2015,1);
INSERT INTO tblDay VALUES (3451,1434153600,13,6,2015,1);
INSERT INTO tblDay VALUES (3452,1434240000,14,6,2015,1);
INSERT INTO tblDay VALUES (3453,1434326400,15,6,2015,1);
INSERT INTO tblDay VALUES (3454,1434412800,16,6,2015,1);
INSERT INTO tblDay VALUES (3455,1434499200,17,6,2015,1);
INSERT INTO tblDay VALUES (3456,1434585600,18,6,2015,1);
INSERT INTO tblDay VALUES (3457,1434672000,19,6,2015,1);
INSERT INTO tblDay VALUES (3458,1434758400,20,6,2015,1);
INSERT INTO tblDay VALUES (3459,1434844800,21,6,2015,1);
INSERT INTO tblDay VALUES (3460,1434931200,22,6,2015,1);
INSERT INTO tblDay VALUES (3461,1435017600,23,6,2015,1);
INSERT INTO tblDay VALUES (3462,1435104000,24,6,2015,1);
INSERT INTO tblDay VALUES (3463,1435190400,25,6,2015,1);
INSERT INTO tblDay VALUES (3464,1435276800,26,6,2015,1);
INSERT INTO tblDay VALUES (3465,1435363200,27,6,2015,1);
INSERT INTO tblDay VALUES (3466,1435449600,28,6,2015,1);
INSERT INTO tblDay VALUES (3467,1435536000,29,6,2015,1);
INSERT INTO tblDay VALUES (3468,1435622400,30,6,2015,1);
INSERT INTO tblDay VALUES (3469,1435708800,1,7,2015,1);
INSERT INTO tblDay VALUES (3470,1435795200,2,7,2015,1);
INSERT INTO tblDay VALUES (3471,1435881600,3,7,2015,1);
INSERT INTO tblDay VALUES (3472,1435968000,4,7,2015,1);
INSERT INTO tblDay VALUES (3473,1436054400,5,7,2015,1);
INSERT INTO tblDay VALUES (3474,1436140800,6,7,2015,1);
INSERT INTO tblDay VALUES (3475,1436227200,7,7,2015,1);
INSERT INTO tblDay VALUES (3476,1436313600,8,7,2015,1);
INSERT INTO tblDay VALUES (3477,1436400000,9,7,2015,1);
INSERT INTO tblDay VALUES (3478,1436486400,10,7,2015,1);
INSERT INTO tblDay VALUES (3479,1436572800,11,7,2015,1);
INSERT INTO tblDay VALUES (3480,1436659200,12,7,2015,1);
INSERT INTO tblDay VALUES (3481,1436745600,13,7,2015,1);
INSERT INTO tblDay VALUES (3482,1436832000,14,7,2015,1);
INSERT INTO tblDay VALUES (3483,1436918400,15,7,2015,1);
INSERT INTO tblDay VALUES (3484,1437004800,16,7,2015,1);
INSERT INTO tblDay VALUES (3485,1437091200,17,7,2015,1);
INSERT INTO tblDay VALUES (3486,1437177600,18,7,2015,1);
INSERT INTO tblDay VALUES (3487,1437264000,19,7,2015,1);
INSERT INTO tblDay VALUES (3488,1437350400,20,7,2015,1);
INSERT INTO tblDay VALUES (3489,1437436800,21,7,2015,1);
INSERT INTO tblDay VALUES (3490,1437523200,22,7,2015,1);
INSERT INTO tblDay VALUES (3491,1437609600,23,7,2015,1);
INSERT INTO tblDay VALUES (3492,1437696000,24,7,2015,1);
INSERT INTO tblDay VALUES (3493,1437782400,25,7,2015,1);
INSERT INTO tblDay VALUES (3494,1437868800,26,7,2015,1);
INSERT INTO tblDay VALUES (3495,1437955200,27,7,2015,1);
INSERT INTO tblDay VALUES (3496,1438041600,28,7,2015,1);
INSERT INTO tblDay VALUES (3497,1438128000,29,7,2015,1);
INSERT INTO tblDay VALUES (3498,1438214400,30,7,2015,1);
INSERT INTO tblDay VALUES (3499,1438300800,31,7,2015,1);
INSERT INTO tblDay VALUES (3500,1438387200,1,8,2015,1);
INSERT INTO tblDay VALUES (3501,1438473600,2,8,2015,1);
INSERT INTO tblDay VALUES (3502,1438560000,3,8,2015,1);
INSERT INTO tblDay VALUES (3503,1438646400,4,8,2015,1);
INSERT INTO tblDay VALUES (3504,1438732800,5,8,2015,1);
INSERT INTO tblDay VALUES (3505,1438819200,6,8,2015,1);
INSERT INTO tblDay VALUES (3506,1438905600,7,8,2015,1);
INSERT INTO tblDay VALUES (3507,1438992000,8,8,2015,1);
INSERT INTO tblDay VALUES (3508,1439078400,9,8,2015,1);
INSERT INTO tblDay VALUES (3509,1439164800,10,8,2015,1);
INSERT INTO tblDay VALUES (3510,1439251200,11,8,2015,1);
INSERT INTO tblDay VALUES (3511,1439337600,12,8,2015,1);
INSERT INTO tblDay VALUES (3512,1439424000,13,8,2015,1);
INSERT INTO tblDay VALUES (3513,1439510400,14,8,2015,1);
INSERT INTO tblDay VALUES (3514,1439596800,15,8,2015,1);
INSERT INTO tblDay VALUES (3515,1439683200,16,8,2015,1);
INSERT INTO tblDay VALUES (3516,1439769600,17,8,2015,1);
INSERT INTO tblDay VALUES (3517,1439856000,18,8,2015,1);
INSERT INTO tblDay VALUES (3518,1439942400,19,8,2015,1);
INSERT INTO tblDay VALUES (3519,1440028800,20,8,2015,1);
INSERT INTO tblDay VALUES (3520,1440115200,21,8,2015,1);
INSERT INTO tblDay VALUES (3521,1440201600,22,8,2015,1);
INSERT INTO tblDay VALUES (3522,1440288000,23,8,2015,1);
INSERT INTO tblDay VALUES (3523,1440374400,24,8,2015,1);
INSERT INTO tblDay VALUES (3524,1440460800,25,8,2015,1);
INSERT INTO tblDay VALUES (3525,1440547200,26,8,2015,1);
INSERT INTO tblDay VALUES (3526,1440633600,27,8,2015,1);
INSERT INTO tblDay VALUES (3527,1440720000,28,8,2015,1);
INSERT INTO tblDay VALUES (3528,1440806400,29,8,2015,1);
INSERT INTO tblDay VALUES (3529,1440892800,30,8,2015,1);
INSERT INTO tblDay VALUES (3530,1440979200,31,8,2015,1);
INSERT INTO tblDay VALUES (3531,1441065600,1,9,2015,1);
INSERT INTO tblDay VALUES (3532,1441152000,2,9,2015,1);
INSERT INTO tblDay VALUES (3533,1441238400,3,9,2015,1);
INSERT INTO tblDay VALUES (3534,1441324800,4,9,2015,1);
INSERT INTO tblDay VALUES (3535,1441411200,5,9,2015,1);
INSERT INTO tblDay VALUES (3536,1441497600,6,9,2015,1);
INSERT INTO tblDay VALUES (3537,1441584000,7,9,2015,1);
INSERT INTO tblDay VALUES (3538,1441670400,8,9,2015,1);
INSERT INTO tblDay VALUES (3539,1441756800,9,9,2015,1);
INSERT INTO tblDay VALUES (3540,1441843200,10,9,2015,1);
INSERT INTO tblDay VALUES (3541,1441929600,11,9,2015,1);
INSERT INTO tblDay VALUES (3542,1442016000,12,9,2015,1);
INSERT INTO tblDay VALUES (3543,1442102400,13,9,2015,1);
INSERT INTO tblDay VALUES (3544,1442188800,14,9,2015,1);
INSERT INTO tblDay VALUES (3545,1442275200,15,9,2015,1);
INSERT INTO tblDay VALUES (3546,1442361600,16,9,2015,1);
INSERT INTO tblDay VALUES (3547,1442448000,17,9,2015,1);
INSERT INTO tblDay VALUES (3548,1442534400,18,9,2015,1);
INSERT INTO tblDay VALUES (3549,1442620800,19,9,2015,1);
INSERT INTO tblDay VALUES (3550,1442707200,20,9,2015,1);
INSERT INTO tblDay VALUES (3551,1442793600,21,9,2015,1);
INSERT INTO tblDay VALUES (3552,1442880000,22,9,2015,1);
INSERT INTO tblDay VALUES (3553,1442966400,23,9,2015,1);
INSERT INTO tblDay VALUES (3554,1443052800,24,9,2015,1);
INSERT INTO tblDay VALUES (3555,1443139200,25,9,2015,1);
INSERT INTO tblDay VALUES (3556,1443225600,26,9,2015,1);
INSERT INTO tblDay VALUES (3557,1443312000,27,9,2015,1);
INSERT INTO tblDay VALUES (3558,1443398400,28,9,2015,1);
INSERT INTO tblDay VALUES (3559,1443484800,29,9,2015,1);
INSERT INTO tblDay VALUES (3560,1443571200,30,9,2015,1);
INSERT INTO tblDay VALUES (3561,1443657600,1,10,2015,1);
INSERT INTO tblDay VALUES (3562,1443744000,2,10,2015,1);
INSERT INTO tblDay VALUES (3563,1443830400,3,10,2015,1);
INSERT INTO tblDay VALUES (3564,1443916800,4,10,2015,1);
INSERT INTO tblDay VALUES (3565,1444003200,5,10,2015,1);
INSERT INTO tblDay VALUES (3566,1444089600,6,10,2015,1);
INSERT INTO tblDay VALUES (3567,1444176000,7,10,2015,1);
INSERT INTO tblDay VALUES (3568,1444262400,8,10,2015,1);
INSERT INTO tblDay VALUES (3569,1444348800,9,10,2015,1);
INSERT INTO tblDay VALUES (3570,1444435200,10,10,2015,1);
INSERT INTO tblDay VALUES (3571,1444521600,11,10,2015,1);
INSERT INTO tblDay VALUES (3572,1444608000,12,10,2015,1);
INSERT INTO tblDay VALUES (3573,1444694400,13,10,2015,1);
INSERT INTO tblDay VALUES (3574,1444780800,14,10,2015,1);
INSERT INTO tblDay VALUES (3575,1444867200,15,10,2015,1);
INSERT INTO tblDay VALUES (3576,1444953600,16,10,2015,1);
INSERT INTO tblDay VALUES (3577,1445040000,17,10,2015,1);
INSERT INTO tblDay VALUES (3578,1445126400,18,10,2015,1);
INSERT INTO tblDay VALUES (3579,1445212800,19,10,2015,1);
INSERT INTO tblDay VALUES (3580,1445299200,20,10,2015,1);
INSERT INTO tblDay VALUES (3581,1445385600,21,10,2015,1);
INSERT INTO tblDay VALUES (3582,1445472000,22,10,2015,1);
INSERT INTO tblDay VALUES (3583,1445558400,23,10,2015,1);
INSERT INTO tblDay VALUES (3584,1445644800,24,10,2015,1);
INSERT INTO tblDay VALUES (3585,1445731200,25,10,2015,1);
INSERT INTO tblDay VALUES (3586,1445817600,26,10,2015,1);
INSERT INTO tblDay VALUES (3587,1445904000,27,10,2015,1);
INSERT INTO tblDay VALUES (3588,1445990400,28,10,2015,1);
INSERT INTO tblDay VALUES (3589,1446076800,29,10,2015,1);
INSERT INTO tblDay VALUES (3590,1446163200,30,10,2015,1);
INSERT INTO tblDay VALUES (3591,1446249600,31,10,2015,1);
INSERT INTO tblDay VALUES (3592,1446336000,1,11,2015,1);
INSERT INTO tblDay VALUES (3593,1446422400,2,11,2015,1);
INSERT INTO tblDay VALUES (3594,1446508800,3,11,2015,1);
INSERT INTO tblDay VALUES (3595,1446595200,4,11,2015,1);
INSERT INTO tblDay VALUES (3596,1446681600,5,11,2015,1);
INSERT INTO tblDay VALUES (3597,1446768000,6,11,2015,1);
INSERT INTO tblDay VALUES (3598,1446854400,7,11,2015,1);
INSERT INTO tblDay VALUES (3599,1446940800,8,11,2015,1);
INSERT INTO tblDay VALUES (3600,1447027200,9,11,2015,1);
INSERT INTO tblDay VALUES (3601,1447113600,10,11,2015,1);
INSERT INTO tblDay VALUES (3602,1447200000,11,11,2015,1);
INSERT INTO tblDay VALUES (3603,1447286400,12,11,2015,1);
INSERT INTO tblDay VALUES (3604,1447372800,13,11,2015,1);
INSERT INTO tblDay VALUES (3605,1447459200,14,11,2015,1);
INSERT INTO tblDay VALUES (3606,1447545600,15,11,2015,1);
INSERT INTO tblDay VALUES (3607,1447632000,16,11,2015,1);
INSERT INTO tblDay VALUES (3608,1447718400,17,11,2015,1);
INSERT INTO tblDay VALUES (3609,1447804800,18,11,2015,1);
INSERT INTO tblDay VALUES (3610,1447891200,19,11,2015,1);
INSERT INTO tblDay VALUES (3611,1447977600,20,11,2015,1);
INSERT INTO tblDay VALUES (3612,1448064000,21,11,2015,1);
INSERT INTO tblDay VALUES (3613,1448150400,22,11,2015,1);
INSERT INTO tblDay VALUES (3614,1448236800,23,11,2015,1);
INSERT INTO tblDay VALUES (3615,1448323200,24,11,2015,1);
INSERT INTO tblDay VALUES (3616,1448409600,25,11,2015,1);
INSERT INTO tblDay VALUES (3617,1448496000,26,11,2015,1);
INSERT INTO tblDay VALUES (3618,1448582400,27,11,2015,1);
INSERT INTO tblDay VALUES (3619,1448668800,28,11,2015,1);
INSERT INTO tblDay VALUES (3620,1448755200,29,11,2015,1);
INSERT INTO tblDay VALUES (3621,1448841600,30,11,2015,1);
INSERT INTO tblDay VALUES (3622,1448928000,1,12,2015,1);
INSERT INTO tblDay VALUES (3623,1449014400,2,12,2015,1);
INSERT INTO tblDay VALUES (3624,1449100800,3,12,2015,1);
INSERT INTO tblDay VALUES (3625,1449187200,4,12,2015,1);
INSERT INTO tblDay VALUES (3626,1449273600,5,12,2015,1);
INSERT INTO tblDay VALUES (3627,1449360000,6,12,2015,1);
INSERT INTO tblDay VALUES (3628,1449446400,7,12,2015,1);
INSERT INTO tblDay VALUES (3629,1449532800,8,12,2015,1);
INSERT INTO tblDay VALUES (3630,1449619200,9,12,2015,1);
INSERT INTO tblDay VALUES (3631,1449705600,10,12,2015,1);
INSERT INTO tblDay VALUES (3632,1449792000,11,12,2015,1);
INSERT INTO tblDay VALUES (3633,1449878400,12,12,2015,1);
INSERT INTO tblDay VALUES (3634,1449964800,13,12,2015,1);
INSERT INTO tblDay VALUES (3635,1450051200,14,12,2015,1);
INSERT INTO tblDay VALUES (3636,1450137600,15,12,2015,1);
INSERT INTO tblDay VALUES (3637,1450224000,16,12,2015,1);
INSERT INTO tblDay VALUES (3638,1450310400,17,12,2015,1);
INSERT INTO tblDay VALUES (3639,1450396800,18,12,2015,1);
INSERT INTO tblDay VALUES (3640,1450483200,19,12,2015,1);
INSERT INTO tblDay VALUES (3641,1450569600,20,12,2015,1);
INSERT INTO tblDay VALUES (3642,1450656000,21,12,2015,1);
INSERT INTO tblDay VALUES (3643,1450742400,22,12,2015,1);
INSERT INTO tblDay VALUES (3644,1450828800,23,12,2015,1);
INSERT INTO tblDay VALUES (3645,1450915200,24,12,2015,1);
INSERT INTO tblDay VALUES (3646,1451001600,25,12,2015,1);
INSERT INTO tblDay VALUES (3647,1451088000,26,12,2015,1);
INSERT INTO tblDay VALUES (3648,1451174400,27,12,2015,1);
INSERT INTO tblDay VALUES (3649,1451260800,28,12,2015,1);
INSERT INTO tblDay VALUES (3650,1451347200,29,12,2015,1);
INSERT INTO tblDay VALUES (3651,1451433600,30,12,2015,1);
INSERT INTO tblDay VALUES (3652,1451520000,31,12,2015,1);
