<?php

namespace App\Mail;

use App\Models\ReportAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ReportAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReportAssignment $assignment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Informe Asignado: ' . $this->assignment->report_type,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.report_assignment',
            with: [
                'userName' => $this->assignment->user->name,
                'reportType' => $this->assignment->report_type,
                'dueDate' => Carbon::parse($this->assignment->due_date)->format('d/m/Y'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
