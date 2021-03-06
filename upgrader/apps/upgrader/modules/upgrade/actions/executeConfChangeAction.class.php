<?php

class executeConfChangeAction extends sfAction {
    
    private $selfConfigPath;
    private $remortConfigPath;
    public function preExecute() {
        $this->getUser()->setAttribute('currentScreen','confInfo');
        $this->applicationRootPath = sfConfig::get('sf_root_dir')."/..";
    }
    
    public function execute($request) {
        $this->form = new ConfigureFile();
        $this->confFileCreted = array('Pending', 'Pending');
        $this->buttonState = "Start";
        if ($request->isMethod('post')) {
            if($request->getParameter('sumbitButton') == 'Proceed') {
                $this->getRequest()->setParameter('submitBy', 'configureFile');
                $this->forward('upgrade','index');
            }
            $this->form->bind($request->getParameter('configureFile'));
            if ($this->form->isValid()) {
                $upgraderUtility = new UpgradeUtility();
                $dbInfo = $this->getUser()->getAttribute('dbInfo');
                $host = $dbInfo['host'];
                $username = $dbInfo['username'];
                $password = $dbInfo['password'];
                $port = $dbInfo['port'];
                $database = $dbInfo['database'];

                $_SESSION['dbHostName'] = $host;
                $_SESSION['dbUserName'] = $username;
                $_SESSION['dbPassword'] = $password;
                $_SESSION['dbName'] = $database;
                $_SESSION['dbHostPort'] = $port;
                
                $upgraderUtility->setApplicationRootPath($this->applicationRootPath);
                $result[] = $upgraderUtility->writeConfFile($host, $port, $database, $username, $password);
                $result[] = $upgraderUtility->writeSymfonyDbConfigFile($host, $port, $database, $username, $password);
                if ($result[0]) {
                    $this->confFileCreted[0] = 'Done';
                }
                if ($result[1]) {
                    $this->confFileCreted[1] = 'Done';
                }
                $success = true;
                foreach ($result as $res) {
                    if (!$res) {
                        $success = false;
                        break;
                    }
                }
                if ($success) {
                    $this->buttonState = 'Proceed';
                    $upgraderUtility->getDbConnection($host, $username, $password, $database, $port);
                    $upgraderUtility->dropUpgradeStatusTable();
                    $startIncrement = $this->getUser()->getAttribute('upgrade.startIncNumber');
                    $endIncrement = $this->getUser()->getAttribute('upgrade.endIncNumber');
                    $startVersion = $this->getUser()->getAttribute('upgrade.currentVersion');
                    $endVersion = $upgraderUtility->getNewVersion();
                    $date = gmdate("Y-m-d H:i:s", time());
                    $result = $upgraderUtility->insertUpgradeHistory($startVersion, $endVersion, $startIncrement, $endIncrement, $date);

                    $upgradeSystemConfiguration = new UpgradeSystemConfiguration();

                    $_SESSION['defUser']['organizationName'] = $upgradeSystemConfiguration->getOrganizationName();
                    $_SESSION['defUser']['organizationEmailAddress'] = $upgradeSystemConfiguration->getAdminEmail();
                    $_SESSION['defUser']['AdminUserName'] = $upgradeSystemConfiguration->getAdminUserName();
                    $_SESSION['defUser']['adminEmployeeFirstName'] = $upgradeSystemConfiguration->getFirstName();
                    $_SESSION['defUser']['adminEmployeeLastName'] = $upgradeSystemConfiguration->getLastName();
                    $_SESSION['defUser']['contactNumber'] = $upgradeSystemConfiguration->getAdminContactNumber();
                    $_SESSION['defUser']['timezone'] = "Not Captured";
                    $_SESSION['defUser']['language'] = $upgradeSystemConfiguration->getLanguage();
                    $_SESSION['defUser']['country'] = $upgradeSystemConfiguration->getCountry();
                    $_SESSION['defUser']['randomNumber'] = rand(1,100);
                    $upgradeSystemConfiguration->setInstanceIdentifier();

                    $upgradeSystemRegistration = new UpgradeOrangehrmRegistration();
                    $upgradeSystemRegistration->sendRegistrationData();
                }
            }
        }
    }
}