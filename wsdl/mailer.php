<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Simple HTML UI_Custom_Mailer Class
 *
 * PHP version 5
 * @category   Mail Server Classes
 * @author     ZFort Group
 * @copyright  (c) 2004-2009 by ZFort Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    SVN: $Id: 257$
 * @link       http://www.zfort.net
 * @since      File available since Release 2.3.0
 */

/**
 * Simple HTML UI_Custom_Mailer Class. Uses standard php functions.
 *
 * @author      ZFort Group
 * @version     $Id: html_mail.class.php,v 2.3.0 2006/12/27 $
 * @access      public
 * @package     PHP2.System
 * @subpackage  Mail
 */
class Mailer{

    /**
     * Headers string
     *
     * @var      string
     * @access   private
     * @see      buildMessage()
     */
    private $_headers;

    /**
     * Headers delimiter string - values (\r\n OR \n)
     *
     * @var      string
     * @access   puplic
     * @see      buildMessage()
     */
    private $_headersDelim = "\n";

    /**
     * Multipart data
     *
     * @var      string
     * @access   private
     */
    private $_multipart;

    /**
     * Mime
     *
     * @var      string
     * @access   private
     */
    private $_mime;

    /**
     * Message HTML content
     *
     * @var      string
     * @access   private
     */
    private $_htmlContent = '';

    /**
     * Added Parts
     *
     * @var      string
     * @access   private
     */
    private $_parts = array();

    /**
     * Mail from parameter
     *
     * @var      string
     * @access   private
     */
    public $mailFrom = false;

    /**
     * Mail Return Path parameter
     *
     * @var      string
     * @access   private
     */
    public $returnPath = false;

    /**
     * Mail Reply To parameter
     *
     * @var      string
     * @access   private
     */
    public $replyTo = false;

    /**
     * Mail to parameters
     *
     * @var      array
     * @access   private
     */
    private $_mailTo = array();

    /**
     * Mail CC parameters
     *
     * @var      array
     * @access   private
     */
    private $_mailCC = array();

    /**
     * Mail BCC parameters
     *
     * @var      array
     * @access   private
     */
    private $_mailBCC = array();
    
    const IS_SMTP = false;

    /**
     * Class constructor.
     *
     * @access  public
     */
    public function __construct($headers = '')
    {
        $this->_headers = $headers;
    }

    /**
     * Adds html content to the message
     *
     * @param   string $html HTML code
     * @access  public
     */
    public function addHtml($html = '')
    {
        $this->_htmlContent .= $html;
    }

    /**
     * Adds Email address
     *
     * @param   string $email Email address
     * @access  public
     */
    public function addAddress($email)
    {
        $this->_mailTo[] = $email;
    }

    /**
     * Adds Email addresses from string
     *
     * @param   string $email Email address
     * @param   string $delim Email delimiter in string
     * @access  public
     */
    public function addAddrFromDelimString($emails, $delim = ';')
    {
        $emailArray = explode($delim, $emails);

        foreach ($emailArray as $email) $this->addAddress($email);
    }

    /**
     * Adds CC Email address
     *
     * @param   string $email Email address
     * @access  public
     */
    public function addCCAddress($email)
    {
        $this->_mailCC[] = $email;
    }

    /**
     * Adds BCC Email address
     *
     * @param   string $email Email address
     * @access  public
     */
    public function addBCCAddress($email)
    {
        $this->_mailBCC[] = $email;
    }

    /**
     * Clears HTML content of the message
     *
     * @access  public
     */
    public function clearHtml()
    {
        $this->_htmlContent = '';
    }

    /**
     * Builds HTML message
     *
     * @param   string $origBoundary Unique timestamp boundary
     * @param   string $codePage Code page Name
     * @access  private
     */
    private function buildHTMLMessage($origBoundary, $codePage, $from = false, $contentType = 'text/html')    {
        if ($from) $this->mailFrom = $from;

        $this->_multipart .= "--$origBoundary".$this->_headersDelim;
        if (($codePage == 'w') || ($codePage == 'win') || ($codePage == 'windows-1251'))        {
            $codePage = 'windows-1251';
        }        elseif (!$codePage)        {
            $codePage = 'koi8-r';
        }

        $this->_multipart .= "Content-Type: $contentType; charset=$codePage".$this->_headersDelim;
        $this->_multipart .= "Content-Transfer-Encoding: Quot-Printed".$this->_headersDelim.$this->_headersDelim;

        if ($this->mailFrom) $this->_headers .= "From: {$this->mailFrom}".$this->_headersDelim;
        if ($this->replyTo) $this->_headers .= 'Reply-To: '.$this->replyTo.$this->_headersDelim;
        if ($this->returnPath) $this->_headers .= 'Return-Path: '.$this->returnPath.$this->_headersDelim;
        if (($ccEmailsStr = $this->getEMailsString($this->_mailCC))) $this->_headers .= "Cc: {$ccEmailsStr}".$this->_headersDelim;
        if (($bccEmailsStr = $this->getEMailsString($this->_mailBCC))) $this->_headers .= "Bcc: {$bccEmailsStr}".$this->_headersDelim;

        $this->_multipart .= $this->_htmlContent.$this->_headersDelim.$this->_headersDelim;
    }

    /**
     * Adds Atachment
     *
     * @param   string $fileContent Content of the file as String
     * @param   string $fileName File name
     * @param   string $contentType File content type
     * @access  public
     */
    public function addAttachment($fileContent, $fileName = 'default.file.name', $contentType = 'application/octet-stream')    {
        $this->_parts[] = array("body" => $fileContent, "name" => $fileName, "c_type" => $contentType);
    }

    /**
     * Builds part of the message
     *
     * @param   integer $partNum Part number
     * @access  private
     */
    private function buildPart($partNum)    {
        $messagePart = '';
        $messagePart .= 'Content-Type: '.$this->_parts[$partNum]['c_type'];

        if ($this->_parts[$partNum]['name'] != '')        {
            $messagePart .= '; name = "'.$this->_parts[$partNum]['name'].'"'.$this->_headersDelim;
        } else {
            $messagePart .= $this->_headersDelim;
        }

        $messagePart .= "Content-Transfer-Encoding: base64".$this->_headersDelim;
        $messagePart .= "Content-Disposition: attachment; filename = \"".$this->_parts[$partNum]['name'].'"'.$this->_headersDelim.$this->_headersDelim;
        $messagePart .= chunk_split(base64_encode($this->_parts[$partNum]['body'])).$this->_headersDelim;

        return $messagePart;
    }


    /**
     * Builds message
     *
     * @param   string $codePage Message code page
     * @access  private
     */
    public function buildMessage($from = false, $codePage = 'us-ascii', $contentType = 'text/html') {// $codePage = 'windows-1251'
        $boundary = "=_".md5(uniqid(time()));

        $this->_headers .= "MIME-Version: 1.0".$this->_headersDelim;
        $this->_headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"".$this->_headersDelim;

        $this->_multipart = "This is a MIME encoded message.".$this->_headersDelim.$this->_headersDelim;

        $this->buildHTMLMessage($boundary, $codePage, $from, $contentType);

        for ($i = (count($this->_parts) - 1); $i >= 0; $i--)
        {
            $this->_multipart .= "--$boundary".$this->_headersDelim.$this->buildPart($i);
        }

        $this->_mime = "$this->_multipart--$boundary--".$this->_headersDelim;
    }

    /**
     * Sends builded message
     *
     * @param   string $eMail EMail address
     * @param   string $subject Message Subject
     * @access  public
     */
    public function send($eMail = false, $subject = '')    {
        if ($eMail) $this->addAddress($eMail);

        $emailList = $this->getEMailsString($this->_mailTo);

        if (!@mail($emailList, $subject, $this->_mime, $this->_headers))        {
            return false;
        } 

        return true;
    }

    
    /**
     * Return emails list with comma delimiters
     *
     * @param   array $emailsArray emails array
     * @return  string
     * @access  public
     */
    private function getEMailsString($emailsArray)    {
        if (!is_array($emailsArray) || !count($emailsArray)) return false;
        $result = '';
        $count = count($emailsArray);
        $i = 1;

        foreach ($emailsArray as $index => $email)        {
            $result .= $email.(($i != $count) ? ',' : '');
            $i++;
        }

        return $result;
    }


    /*function fax($fax_number, $file_content) {
        if (!exec("sendfax -n -d $fax_number $file_content >/dev/null/ 2>&1 &")) {
            return (false);
        }
        return (true);
    }*/

}

?>
