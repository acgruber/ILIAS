<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionRelatedNavigationBarGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $instantResponseCmd;

    protected $instantResponseEnabled;

    protected $hintProvidingEnabled;

    protected $hintRequestsPossible;

    protected $hintRequestsExist;

    protected $hintRequestCmd;

    protected $hintListCmd;

    public function __construct(ilCtrl $ctrl, ilLanguage $lng)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }

    public function getHintListCmd()
    {
        return $this->hintListCmd;
    }

    public function setHintListCmd($hintListCmd): void
    {
        $this->hintListCmd = $hintListCmd;
    }

    public function getHintRequestCmd()
    {
        return $this->hintRequestCmd;
    }

    public function setHintRequestCmd($hintRequestCmd): void
    {
        $this->hintRequestCmd = $hintRequestCmd;
    }

    public function setHintRequestsExist($hintRequestsExist): void
    {
        $this->hintRequestsExist = $hintRequestsExist;
    }

    public function doesHintRequestsExist()
    {
        return $this->hintRequestsExist;
    }

    public function setHintRequestsPossible($hintRequestsPossible): void
    {
        $this->hintRequestsPossible = $hintRequestsPossible;
    }

    public function areHintRequestsPossible()
    {
        return $this->hintRequestsPossible;
    }

    public function setHintProvidingEnabled($hintProvidingEnabled): void
    {
        $this->hintProvidingEnabled = $hintProvidingEnabled;
    }

    public function isHintProvidingEnabled()
    {
        return $this->hintProvidingEnabled;
    }

    public function setInstantResponseEnabled($instantFeedbackEnabled): void
    {
        $this->instantResponseEnabled = $instantFeedbackEnabled;
    }

    public function isInstantResponseEnabled()
    {
        return $this->instantResponseEnabled;
    }

    public function setInstantResponseCmd($instantResponseCmd): void
    {
        $this->instantResponseCmd = $instantResponseCmd;
    }

    public function getInstantResponseCmd()
    {
        return $this->instantResponseCmd;
    }

    public function getHTML(): string
    {
        $navTpl = new ilTemplate('tpl.qst_question_related_navigation.html', true, true, 'Modules/TestQuestionPool');

        $parseQuestionRelatedNavigation = false;

        if ($this->isInstantResponseEnabled()) {
            $navTpl->setCurrentBlock("direct_feedback");
            $navTpl->setVariable("CMD_SHOW_INSTANT_RESPONSE", $this->getInstantResponseCmd());
            $navTpl->setVariable("TEXT_SHOW_INSTANT_RESPONSE", $this->lng->txt("check"));
            $navTpl->parseCurrentBlock();

            $parseQuestionRelatedNavigation = true;
        }

        if ($this->isHintProvidingEnabled()) {
            require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
            require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';

            if ($this->areHintRequestsPossible()) {
                if ($this->doesHintRequestsExist()) {
                    $buttonText = $this->lng->txt("button_request_next_question_hint");
                } else {
                    $buttonText = $this->lng->txt("button_request_question_hint");
                }

                $navTpl->setCurrentBlock("button_request_next_question_hint");
                $navTpl->setVariable("CMD_REQUEST_NEXT_QUESTION_HINT", $this->getHintRequestCmd());
                $navTpl->setVariable("TEXT_REQUEST_NEXT_QUESTION_HINT", $buttonText);
                $navTpl->parseCurrentBlock();

                $parseQuestionRelatedNavigation = true;
            }

            if ($this->doesHintRequestsExist()) {
                $navTpl->setCurrentBlock("button_show_requested_question_hints");
                $navTpl->setVariable("CMD_SHOW_REQUESTED_QUESTION_HINTS", $this->getHintListCmd());
                $navTpl->setVariable("TEXT_SHOW_REQUESTED_QUESTION_HINTS", $this->lng->txt("show_requested_question_hints"));
                $navTpl->parseCurrentBlock();

                $parseQuestionRelatedNavigation = true;
            }
        }

        if ($parseQuestionRelatedNavigation) {
            $navTpl->setCurrentBlock("question_related_navigation");
            $navTpl->parseCurrentBlock();
        }

        return $navTpl->get();
    }
}
