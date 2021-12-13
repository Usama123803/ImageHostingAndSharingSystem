<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $url;
    public $email;
    public $jwt_token;
    public function __construct($jwt_token,$email,$url)
    {
        $this->jwt_token = $jwt_token;
        $this->email     = $email;
        $this->url       = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('view.name');
        return $this->subject('Link Send Succesfully')->markdown('reset_Password_Link');
    }
}
