<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class HomeownerSupportMail extends Mailable
{
    public $user;
    public $subjectText;
    public $body;

    public function __construct($user, $subject, $body)
    {
        $this->user = $user;
        $this->subjectText = $subject;
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject("Support Request: " . $this->subjectText)
            ->view('emails.homeowner-support');
    }
}
