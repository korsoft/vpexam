<?php
require_once $_SERVER['DOCUMENT_ROOT'] .'/libs/MadMimi.class.php';

class Mailer {
    CONST SENDER_MAIL = 'webmaster@vpexam.com';
    CONST SENDER_NAME = 'Webmaster';

    private $mimi;
	private $_invalidemails = [];
	private $_recipients    = [];
    private $_sender        = [];
	private $_subject       = '';
	private $_template      = '';
	private $_templatespath = 'templates/';
    public function __construct($templatename = '', $subject = '', $recipients = '', $content) {
        $this->mimi = new MadMimi('louis@korsoftcorp.com', '11b0b9a05fcfedc1db23787742664991');
    	$this->setTemplate($templatename);
    	$this->setSubject($subject);
     	$this->setRecipients($recipients);
        $this->setContent($content);
    }
    function setContent($content) {
        if('' == $this->_template) {
            throw new Exception('Need to add the template before integrate the content', 1);
        }
        if(is_array($content) && 0 < count($content)) {
            foreach($content as $key => $value) {
                $this->_template = str_replace('{{' . $key . '}}', $value, $this->_template);
            }
        }
    }
    function setRecipients($recipients) {
        //Si es string y diferente de vacio
        if(is_string($recipients) && '' != $recipients) {
            //Si el string tiene al menos una coma
            if(0 <= strpos($recipients, ',')) {
                //Separamos el string por coma, creando un array de emails
                $recipients = explode(',', $recipients);
            }
            else {
                //Creamos un arreglo con el string
                $recipients = [$recipients];
            }
        }
        //Si es un arreglo y tiene al menos un elemento
        if(is_array($recipients) && 0 < count($recipients)) {
            //Validamos si los correos dentro del arreglo son validos y los invalidos los agregamos a un arreglo
            $this->_recipients = array_filter($recipients, function($recipient) {
                $isvalid = filter_var($recipient['email'], FILTER_VALIDATE_EMAIL);
                if(false == $isvalid) {
                    $this->_invalidemails[] = $recipient;
                }
                return $isvalid;
            });
        }
        //Regresamos la lista de correos invalidos
        return $this->_invalidemails;
    }
    function setSubject($subject) {
    	$this->_subject = $subject;
    }
    function setTemplate($templatename) {
        $path = $_SERVER['DOCUMENT_ROOT'] . "/{$this->_templatespath}{$templatename}.html";
        if('' !== $templatename && file_exists($path)) {
            $this->_template = file_get_contents($path);
        }
    }
    function send() {
        $body = $this->_template;
        if(0 <= strpos($this->_subject, '{{sender}}')) {
            $this->_subject = str_replace('{{sender}}', $this->_sender['name'], $this->_subject);
        }
        if(0 <= strpos($this->_template, '{{title}}')) {
            $this->_template = str_replace('{{title}}', $this->_subject, $this->_template);
        }
        if(0 <= strpos($this->_template, '{{sender}}')) {
            $this->_template = str_replace('{{sender}}', $this->_sender['name'], $this->_template);
        }
        foreach ($this->_recipients as $recipient) {
            if(0 <= strpos($this->_template, '{{name}}')) {
                $body = preg_replace('/[\t\n]/', '', str_replace('{{name}}', $recipient['name'], $this->_template));
            }
            $options = [
                'recipients'     => "{$recipient['name']} <{$recipient['email']}>", 
                'promotion_name' => 'VPExam', 
                'subject'        => $this->_subject, 
                'from'           => ( Mailer::SENDER_NAME ." <" . Mailer::SENDER_MAIL . ">" ),
            ];
            try {
                $this->mimi->SendHTML($options, $body);
            } catch (Exception $e) {
                error_log(__METHOD__ . '::Error: ' . $e->getResponse()->getBody()->getContents());
            }
        }
    }
}
