<?php

namespace Istrix\Mail;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\SmartObject;
use SendGrid;
use SendGrid\Mail\Mail;

class SendgridMailer implements IMailer
{
    use SmartObject;

    const ENDPOINT = "https://api.sendgrid.com/";

    /** @var string */
    private $key;

    /** @var string */
    private $tempFolder;

    /** @var array */
    private $tempFiles = [];

    /**
     * MailSender constructor
     *
     * @param string $key
     * @param string $tempFolder
     */
    public function __construct($key, $tempFolder)
    {
        $this->key = $key;
        $this->tempFolder = $tempFolder;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Sends email to sendgrid
     *
     * @param Message $message
     */
    public function send(Message $message)
    {
        $sendGrid = new SendGrid($this->key);
        $email = new Mail();

        $from = $message->getFrom();
        reset($from);
        $key = key($from);

        $email->setFrom($key, $from[$key]);
        $email->setSubject($message->getSubject());
        $email->addContent("text/plain", $message->getBody());
        $email->addContent("text/html", $message->getHtmlBody());

        foreach ($message->getAttachments() as $attachement) {
            $header = $attachement->getHeader('Content-Disposition');
            preg_match('/filename\=\"(.*)\"/', $header, $result);
            $originalFileName = $result[1];

            $filePath = $this->saveTempAttachement($attachement->getBody());

            $email->addAttachment($filePath, $originalFileName);
        }

        $headerTo = $message->getHeader('To');
        if ($headerTo !== null) {
            foreach ($headerTo as $recipient => $name) {
                $email->addTo($recipient);
            }
        }

        $headerCC = $message->getHeader('Cc') or [];
        if ($headerCC !== null) {
            foreach ($headerCC as $recipient => $name) {
                $email->addCc($recipient);
            }
        }

        $headerBcc = $message->getHeader('Bcc') or [];
        if ($headerBcc !== null) {
            foreach ($headerBcc as $recipient => $name) {
                $email->addBcc($recipient);
            }
        }

        $sendGrid->send($email);

        $this->cleanUp();
    }

    private function saveTempAttachement($body)
    {
        $filePath = $this->tempFolder . '/' . md5($body);
        file_put_contents($filePath, $body);
        array_push($this->tempFiles, $filePath);

        return $filePath;
    }

    private function cleanUp()
    {
        foreach ($this->tempFiles as $file) {
            unlink($file);
        }
    }

}
