<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\GeneratedReport;
use App\Models\ReportTemplate;

class ReportGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $generatedReport;
    public $template;

    /**
     * Create a new message instance.
     */
    public function __construct(GeneratedReport $generatedReport, ReportTemplate $template)
    {
        $this->generatedReport = $generatedReport;
        $this->template = $template;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Scheduled Report: {$this->template->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.report-generated',
            with: [
                'reportName' => $this->template->name,
                'reportType' => ucfirst(str_replace('_', ' ', $this->template->type)),
                'recordCount' => $this->generatedReport->record_count,
                'generatedAt' => $this->generatedReport->generated_at->format('F d, Y H:i:s'),
                'format' => strtoupper($this->generatedReport->format),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $filePath = storage_path("app/public/{$this->generatedReport->file_path}");
        
        if (file_exists($filePath)) {
            return [
                Attachment::fromPath($filePath)
                    ->as(basename($this->generatedReport->file_path))
                    ->withMime($this->getMimeType($this->generatedReport->format)),
            ];
        }

        return [];
    }

    /**
     * Get MIME type for format
     */
    private function getMimeType($format)
    {
        return match($format) {
            'csv' => 'text/csv',
            'excel', 'xlsx' => 'application/vnd.ms-excel',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
