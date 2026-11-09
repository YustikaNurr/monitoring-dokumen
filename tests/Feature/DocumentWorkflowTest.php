<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentWorkflow;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_can_be_created_with_initial_workflow_and_attachment(): void
    {
        Storage::fake('local');

        $senderUnit = Unit::query()->create(['name' => 'BSRM']);
        $documentType = DocumentType::query()->create(['name' => 'PDRK']);

        $response = $this->post(route('documents.store'), [
            'document_number' => 'ND.003',
            'received_at' => '2026-08-15',
            'subject' => 'RRA Budget',
            'document_type_id' => $documentType->id,
            'sender_unit_id' => $senderUnit->id,
            'initial_pic' => 'Sinta',
            'notes' => 'Dokumen dibuat',
            'attachments' => [UploadedFile::fake()->create('budget.pdf', 120, 'application/pdf')],
        ]);

        $response->assertRedirect();

        $document = Document::query()->where('document_number', 'ND.003')->firstOrFail();

        $this->assertDatabaseHas('document_workflows', [
            'document_id' => $document->id,
            'from_unit_id' => null,
            'to_unit_id' => $senderUnit->id,
            'pic' => 'Sinta',
            'status' => DocumentWorkflow::STATUS_ON_PROCESS,
            'direction' => DocumentWorkflow::DIRECTION_CREATED,
        ]);

        $this->assertDatabaseCount('attachments', 1);
        Storage::disk('local')->assertExists($document->attachments()->firstOrFail()->file_path);
    }

    public function test_document_can_be_returned_to_previous_unit(): void
    {
        $bsrm = Unit::query()->create(['name' => 'BSRM']);
        $finance = Unit::query()->create(['name' => 'Finance']);
        $documentType = DocumentType::query()->create(['name' => 'PDRK']);

        $document = Document::query()->create([
            'document_number' => 'ND.004',
            'received_at' => '2026-08-15',
            'subject' => 'Revisi Budget',
            'document_type_id' => $documentType->id,
            'sender_unit_id' => $bsrm->id,
            'initial_pic' => 'Sinta',
        ]);

        $document->workflows()->create([
            'from_unit_id' => null,
            'to_unit_id' => $bsrm->id,
            'pic' => 'Sinta',
            'status' => DocumentWorkflow::STATUS_ON_PROCESS,
            'direction' => DocumentWorkflow::DIRECTION_CREATED,
        ]);

        $document->workflows()->create([
            'from_unit_id' => $bsrm->id,
            'to_unit_id' => $finance->id,
            'pic' => 'Andi',
            'status' => DocumentWorkflow::STATUS_ON_PROCESS,
            'direction' => DocumentWorkflow::DIRECTION_SENT,
        ]);

        $response = $this->post(route('documents.workflows.store', $document), [
            'action' => 'return',
            'pic' => 'Sinta',
            'note' => 'Revisi budget',
        ]);

        $response->assertRedirect(route('documents.show', $document));

        $this->assertDatabaseHas('document_workflows', [
            'document_id' => $document->id,
            'from_unit_id' => $finance->id,
            'to_unit_id' => $bsrm->id,
            'pic' => 'Sinta',
            'status' => DocumentWorkflow::STATUS_RETURN,
            'direction' => DocumentWorkflow::DIRECTION_RETURNED,
        ]);
    }
}
