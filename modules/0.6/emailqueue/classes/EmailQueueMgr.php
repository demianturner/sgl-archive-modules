<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007, Demian Turner                                     |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6.3                                                             |
// +---------------------------------------------------------------------------+
// | EmailqueueMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Peter Termaten <peter.termaten@gmail.com>                         |
// +---------------------------------------------------------------------------+

require_once SGL_CORE_DIR . '/Emailer/Queue.php';

/**
 * Admin functions for QueueMgr module
 *
 * @package emailqueue
 * @author  Peter Termaten <peter.termaten@gmail.com>
 */
class EmailQueueMgr extends SGL_Manager
{
    public function __construct()
    {
    SGL::logMessage(null, PEAR_LOG_DEBUG);
    parent::SGL_Manager();

    $this->_aActionsMapping =  array(
             'list'         => array('list', 'cliResult'),
             'empty_queue'  => array('empty_queue','cliResult'),
                                    );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->pageTitle      = $this->pageTitle;
        $input->template       = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action         = $req->get('action')
            ? $req->get('action') : 'list';

        $input->terminalOutput = '';
        $input->groupID        =  $req->get('groupID')? (int)$req->get('groupID') : NULL;
    }

    /**
     * By default we just show availabe actions.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_list($input, $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->terminalOutput .= "
        \nAvailable actions:\n\n"
            . " 1. empty_queue - send emails from email_queue table\n"
            . "    Parameters:\n"
            . "     --groupID - send only mails for this group, default: all groups\n";
    }

    /**
     * Send the emails using a MTA from the email_queue table.
     *
     * Example usage:
     * php www/index.php --moduleName=emailqueue --managerName=EmailQueueMgr
     *       --action=empty_queue --groupID=1
     * see conf.ini for configurable options
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_empty_queue(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aOptions = $this->conf['EmailQueueMgr']
            ? $this->conf['EmailQueueMgr']
            : array();
        $emailerClass = $this->_getEmailerClass();
        $mail_queue = new $emailerClass($aOptions);

        //sending the messages
        $res = $mail_queue->processQueue($input->groupID);

        if ($res === true) {
            $input->terminalOutput .= "\nMail_queue processed correctly\n";
        } else {
            $input->terminalOutput .= "No emails sent from email_queue, mail problem?\n";
        }

    }

    private function _getEmailerClass()
    {
        if (SGL_Config::get('EmailQueueMgr.customEmailer')) {
            $className = SGL_Config::get('EmailQueueMgr.customEmailer');
            $path = trim(preg_replace('/_/', '/', $className)) . '.php';
            require_once SGL_LIB_DIR . '/' . $path;
        } else {
            $className = 'SGL_Emailer_Queue';
        }
        return $className;
    }

     /**
     * Action, which outputs CLI result.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_cliResult($input, $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        echo $input->terminalOutput;
        exit;
    }
}
?>
<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007, Demian Turner                                     |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6.3                                                             |
// +---------------------------------------------------------------------------+
// | EmailqueueMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Peter Termaten <peter.termaten@gmail.com>                         |
// +---------------------------------------------------------------------------+

require_once SGL_CORE_DIR . '/Emailer/Queue.php';

/**
 * Admin functions for QueueMgr module
 *
 * @package emailqueue
 * @author  Peter Termaten <peter.termaten@gmail.com>
 */
class EmailQueueMgr extends SGL_Manager
{
    public function __construct()
    {
    SGL::logMessage(null, PEAR_LOG_DEBUG);
    parent::SGL_Manager();

    $this->_aActionsMapping =  array(
             'list'         => array('list', 'cliResult'),
             'empty_queue'  => array('empty_queue','cliResult'),
                                    );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->pageTitle      = $this->pageTitle;
        $input->template       = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action         = $req->get('action')
            ? $req->get('action') : 'list';

        $input->terminalOutput = '';
        $input->groupID        =  $req->get('groupID')? (int)$req->get('groupID') : NULL;
    }

    /**
     * By default we just show availabe actions.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_list($input, $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->terminalOutput .= "
        \nAvailable actions:\n\n"
            . " 1. empty_queue - send emails from email_queue table\n"
            . "    Parameters:\n"
            . "     --groupID - send only mails for this group, default: all groups\n";
    }

    /**
     * Send the emails using a MTA from the email_queue table.
     *
     * Example usage:
     * php www/index.php --moduleName=emailqueue --managerName=EmailQueueMgr
     *       --action=empty_queue --groupID=1
     * see conf.ini for configurable options
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_empty_queue(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aOptions = $this->conf['EmailQueueMgr']
            ? $this->conf['EmailQueueMgr']
            : array();
        $emailerClass = $this->_getEmailerClass();
        $mail_queue = new $emailerClass($aOptions);

        //sending the messages
        $res = $mail_queue->processQueue($input->groupID);

        if ($res === true) {
            $input->terminalOutput .= "\nMail_queue processed correctly\n";
        } else {
            $input->terminalOutput .= "No emails sent from email_queue, mail problem?\n";
        }

    }

    private function _getEmailerClass()
    {
        if (SGL_Config::get('EmailQueueMgr.customEmailer')) {
            $className = SGL_Config::get('EmailQueueMgr.customEmailer');
            $path = trim(preg_replace('/_/', '/', $className)) . '.php';
            require_once SGL_LIB_DIR . '/' . $path;
        } else {
            $className = 'SGL_Emailer_Queue';
        }
        return $className;
    }

     /**
     * Action, which outputs CLI result.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_cliResult($input, $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        echo $input->terminalOutput;
        exit;
    }
}
?>
