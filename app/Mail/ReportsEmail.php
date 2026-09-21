<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportsEmail extends Mailable
{
     use Queueable, SerializesModels;

    public array $files;
    public string $name;
    public string $title;
    public string $customer_name;
    public $company;
    public array $reports;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $files, string $name, string $customer_name, string $title, $company, array $reports)
    {
        $this->files = $files;
        $this->name  = $name;
        $this->title = $title;
        $this->customer_name = $customer_name;
        $this->company = $company;
        $this->reports = $reports;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->title)
        ->view('emails.email')
        ->with([
            'name'  => $this->name,
            'title' => $this->title,
            'customer_name' => $this->customer_name,
            'company' => $this->company,
            'reports' => $this->reports,
        ]);  
                   
        foreach ($this->files as $file) {
            $mail->attach($file);
        }

        return $mail;
    }
}
