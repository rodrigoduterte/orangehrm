<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 *
 */

/**
 * Form class for search candidates
 */
class viewCandidatesForm extends BaseForm {

    private $candidateService;
    private $vacancyService;
    private $allowedCandidateList;
    private $allowedVacancyList;
    public  $allowedCandidateListToDelete;

    /**
     * Get CandidateService
     * @returns CandidateService
     */
    public function getCandidateService() {
        if (is_null($this->candidateService)) {
            $this->candidateService = new CandidateService();
            $this->candidateService->setCandidateDao(new CandidateDao());
        }
        return $this->candidateService;
    }

    /**
     * Set CandidateService
     * @param CandidateService $candidateService
     */
    public function setCandidateService(CandidateService $candidateService) {
        $this->candidateService = $candidateService;
    }

    /**
     * Get VacancyService
     * @returns VacncyService
     */
    public function getVacancyService() {
        if (is_null($this->vacancyService)) {
            $this->vacancyService = new VacancyService();
            $this->vacancyService->setVacancyDao(new VacancyDao());
        }
        return $this->vacancyService;
    }

    /**
     * Set VacancyService
     * @param VacancyService $vacancyService
     */
    public function setVacancyService(VacancyService $vacancyService) {
        $this->vacancyService = $vacancyService;
    }

    /**
     *
     */
    public function configure() {

        $this->allowedCandidateList = $this->getOption('allowedCandidateList');
        $this->allowedVacancyList = $this->getOption('allowedVacancyList');
        $this->allowedCandidateListToDelete = $this->getOption('allowedCandidateListToDelete');
        $jobVacancyList = $this->getVacancyList();
        $modeOfApplication = array('' => __('All'), JobCandidate::MODE_OF_APPLICATION_MANUAL => __('Manual'), JobCandidate::MODE_OF_APPLICATION_ONLINE => __('Online'));
        $hiringManagerList = $this->getHiringManagersList();
        $jobTitleList = $this->getJobTitleList();
        $statusList = $this->getStatusList();
        //creating widgets
        $this->setWidgets(array(
            'jobTitle' => new sfWidgetFormSelect(array('choices' => $jobTitleList)),
            'jobVacancy' => new sfWidgetFormSelect(array('choices' => $jobVacancyList)),
            'hiringManager' => new sfWidgetFormSelect(array('choices' => $hiringManagerList)),
            'status' => new sfWidgetFormSelect(array('choices' => $statusList)),
            'candidateName' => new sfWidgetFormInputText(),
            'selectedCandidate' => new sfWidgetFormInputHidden(),
            'keywords' => new sfWidgetFormInputText(),
            'modeOfApplication' => new sfWidgetFormSelect(array('choices' => $modeOfApplication)),
            'fromDate' => new sfWidgetFormInputText(),
            'toDate' => new sfWidgetFormInputText(),
        ));

        $inputDatePattern = sfContext::getInstance()->getUser()->getDateFormat();

        //Setting validators
        $this->setValidators(array(
            'jobTitle' => new sfValidatorString(array('required' => false)),
            'jobVacancy' => new sfValidatorString(array('required' => false)),
            'hiringManager' => new sfValidatorString(array('required' => false)),
            'status' => new sfValidatorString(array('required' => false)),
            'candidateName' => new sfValidatorString(array('required' => false)),
            'selectedCandidate' => new sfValidatorNumber(array('required' => false, 'min' => 0)),
            'keywords' => new sfValidatorString(array('required' => false)),
            'modeOfApplication' => new sfValidatorString(array('required' => false)),
            'fromDate' => new ohrmDateValidator(array('date_format' => $inputDatePattern, 'required' => false),
                    array('invalid' => 'Date format should be ' . strtoupper($inputDatePattern))),
            'toDate' => new ohrmDateValidator(array('date_format' => $inputDatePattern, 'required' => false),
                    array('invalid' => 'Date format should be ' . strtoupper($inputDatePattern))),
        ));
        $this->widgetSchema->setNameFormat('candidateSearch[%s]');
    }

    /**
     *
     * @param CandidateSearchParameters $searchParam
     * @return CandidateSearchParameters
     */
    public function getSearchParamsBindwithFormData(CandidateSearchParameters $searchParam) {

        $searchParam->setJobTitleCode($this->getValue('jobTitle'));
        $searchParam->setVacancyId($this->getValue('jobVacancy'));
        $searchParam->setHiringManagerId($this->getValue('hiringManager'));
        $searchParam->setStatus($this->getValue('status'));
        $searchParam->setCandidateId($this->getValue('selectedCandidate'));
        $searchParam->setModeOfApplication($this->getValue('modeOfApplication'));
        $searchParam->setFromDate($this->getValue('fromDate'));
        $searchParam->setToDate($this->getValue('toDate'));
        $searchParam->setKeywords($this->getValue('keywords'));
        $searchParam->setCandidateName($this->getValue('candidateName'));

        return $searchParam;
    }

    /**
     *
     * @param CandidateSearchParameters $searchParam
     */
    public function setDefaultDataToWidgets(CandidateSearchParameters $searchParam) {

        $newSearchParam = new CandidateSearchParameters();

        $this->setDefault('jobTitle', $searchParam->getJobTitleCode());
        $this->setDefault('jobVacancy', $searchParam->getVacancyId());
        $this->setDefault('hiringManager', $searchParam->getHiringManagerId());
        $this->setDefault('status', $searchParam->getStatus());
        $this->setDefault('selectedCandidate', $searchParam->getCandidateId());
        $this->setDefault('modeOfApplication', $searchParam->getModeOfApplication());

        $displayFromDate = ($searchParam->getFromDate() == $newSearchParam->getFromDate()) ? "" : $searchParam->getFromDate();
        $displayToDate = ($searchParam->getToDate() == $newSearchParam->getToDate()) ? "" : $searchParam->getToDate();

        $this->setDefault('fromDate', $displayFromDate);
        $this->setDefault('toDate', $displayToDate);
        $this->setDefault('keywords', $searchParam->getKeywords());
        $this->setDefault('candidateName', $searchParam->getCandidateName());
    }

    /**
     * Returns job Title List
     * @return array
     */
    private function getJobTitleList() {
        $list = array("" => __('All'));
        $jobService = new JobService();
        $jobTitleList = $jobService->getJobTitleList();
        foreach ($jobTitleList as $jobTitle) {
            $list[$jobTitle->getId()] = $jobTitle->getName();
        }
        return $list;
    }

    /**
     * Make status List
     * @return array
     */
    private function getStatusList() {
        $list = array("" => __('All'));
        $userObj = sfContext::getInstance()->getUser()->getAttribute('user');
        $allowedStates = $userObj->getAllAlowedRecruitmentApplicationStates(PluginWorkflowStateMachine::FLOW_RECRUITMENT);
        $uniqueStatesList = array_unique($allowedStates);

        foreach ($uniqueStatesList as $state) {
            $list[$state] = ucwords(strtolower($state));
        }
        return $list;
    }

    /**
     * Returns HiringManager List
     * @return array
     */
    private function getHiringManagersList() {
        $list = array("" => __('All'));
        $hiringManagersList = $this->getVacancyService()->getHiringManagersList("", "", $this->allowedVacancyList);
        foreach ($hiringManagersList as $hiringManager) {
            $list[$hiringManager['id']] = $hiringManager['name'];
        }

        return $list;
    }

    /**
     * Returns Vacancy List
     * @return array
     */
    private function getVacancyList() {
        $list = array("" => __('All'));
        $vacancyList = $this->getVacancyService()->getAllVacancies();
        foreach ($vacancyList as $vacancy) {
            $list[$vacancy->getId()] = $vacancy->getName();
        }
        return $list;
    }

    /**
     * Returns Action List
     * @return array
     */
    private function getActionList() {

        $list = array("" => __('All'));
        $userObj = sfContext::getInstance()->getUser()->getAttribute('user');
        $allowedActions = $userObj->getAllowedActions(PluginWorkflowStateMachine::FLOW_RECRUITMENT, "");

        foreach ($allowedActions as $action) {
            if ($action != 0) {
                $list[$action] = $this->getActionName($action);
            }
        }
        return $list;
    }

    /**
     * Returns Candidate json list
     * @return JsonCandidate List
     */
    public function getCandidateListAsJson() {

        $jsonArray = array();
        $escapeCharSet = array(38, 34, 60, 61, 62, 63, 64, 58, 59, 94, 96);
        $candidateList = $this->getCandidateService()->getCandidateList($this->allowedCandidateList);
        foreach ($candidateList as $candidate) {

            $name = trim($candidate->getFullName());

            foreach ($escapeCharSet as $char) {
                $name = str_replace(chr($char), (chr(92) . chr($char)), $name);
            }

            $jsonArray[] = array('name' => $name, 'id' => $candidate->getId());
        }
        $jsonString = json_encode($jsonArray);
        return $jsonString;
    }

}

