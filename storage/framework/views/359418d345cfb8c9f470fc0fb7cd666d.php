<?php
    $currentWorkflow = $document->latestWorkflow;
    $currentStatus = $document->currentStatus();
    $badgeClass = match ($currentStatus) {
        \App\Models\DocumentWorkflow::STATUS_RETURN => 'return',
        \App\Models\DocumentWorkflow::STATUS_APPROVED => 'approved',
        \App\Models\DocumentWorkflow::STATUS_REJECTED => 'rejected',
        \App\Models\DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI => 'waiting',
        \App\Models\DocumentWorkflow::STATUS_COMPLETED => 'success',
        default => 'on-process',
    };
    $isWaitingConfirmation = in_array($currentStatus, [
        \App\Models\DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI,
        \App\Models\DocumentWorkflow::STATUS_RETURN,
    ], true);
    $isCompleted = $currentStatus === \App\Models\DocumentWorkflow::STATUS_COMPLETED;
    $lastWorkflow = $document->workflows->last();
    $isReturnedConfirmation = $currentStatus === \App\Models\DocumentWorkflow::STATUS_RETURN
        || $lastWorkflow?->direction === \App\Models\DocumentWorkflow::DIRECTION_RETURNED;
    $confirmationNote = $currentWorkflow?->note;
    $isFirstWorkflow = $document->latestWorkflow?->direction === \App\Models\DocumentWorkflow::DIRECTION_CREATED
        && $document->latestWorkflow?->from_unit_id === null;
    $allowedActions = match ($currentStatus) {
        \App\Models\DocumentWorkflow::STATUS_ON_PROCESS => $isFirstWorkflow
            ? ['disposisi']
            : ['approved', 'return', 'rejected'],
        \App\Models\DocumentWorkflow::STATUS_APPROVED => ['disposisi', 'completed'],
        \App\Models\DocumentWorkflow::STATUS_RETURN,
        \App\Models\DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI,
        \App\Models\DocumentWorkflow::STATUS_COMPLETED => [],
        \App\Models\DocumentWorkflow::STATUS_REJECTED => ['completed'],
        default => [],
    };
?>



<?php $__env->startSection('content'); ?>
<style>
    .document-detail { width: 100%; color: var(--text); }
    .document-detail .card { border: 1px solid var(--line); border-radius: 20px; background: #FFFFFF; box-shadow: 0 1px 3px rgba(17,24,39,.06), 0 8px 24px rgba(17,24,39,.04); }
    .document-hero { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 20px; padding: 28px 32px; background: linear-gradient(135deg, #FDECEE 0%, #FFFFFF 72%); }
    .document-hero-content { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 24px; }
    .hero-label { margin-bottom: 6px; color: #DC1F2B; font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .document-hero h2 { margin: 0 0 5px; color: #111827; font-size: 26px; font-weight: 700; line-height: 1.25; }
    .document-hero p { margin: 0; color: #6B7280; font-size: 14px; line-height: 1.5; }
    .hero-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
    .hero-actions .button { min-height: 40px; padding: 10px 16px; border-radius: 999px; box-shadow: none; }
    .hero-actions .button.secondary { border: 1px solid #E5E7EB; background: #FFFFFF; color: #374151; }
    .hero-actions .button:not(.secondary) { background: #DC1F2B; color: #FFFFFF; }
    .progress-card { margin-bottom: 24px; padding: 24px; }
    .progress-summary { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; }
    .progress-status .label { margin-bottom: 7px; color: #6B7280; font-size: 11px; font-weight: 700; letter-spacing: .1em; }
    .progress-status .badge { padding: 6px 12px; }
    .progress-aging { display: inline-flex; align-items: center; min-height: 32px; padding: 6px 12px; border: 1px solid transparent; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .progress-aging.on-process { color: #1D4ED8; background: #E8F0FE; border-color: #BFD3FB; }
    .progress-aging.return { color: #C2410C; background: #FFF0E5; border-color: #F9CBA6; }
    .progress-aging.waiting { color: #92400E; background: #FEF3C7; border-color: #F5DC8A; }
    .progress-aging.approved { color: #15803D; background: #E6F6EC; border-color: #BFE5CC; }
    .progress-aging.rejected { color: #9B1C1C; background: #FDECEC; border-color: #F4C2C2; }
    .progress-aging.success { color: #6D28D9; background: #F0EAFD; border-color: #D8C8F7; }
    .progress-track { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-top: 22px; }
    .progress-step { color: #6B7280; font-size: 12px; font-weight: 700; line-height: 1.4; text-align: center; }
    .progress-step::before { content: ''; display: block; height: 6px; margin-bottom: 9px; border-radius: 999px; background: #E5E7EB; }
    .progress-step.done, .progress-step.current { color: #DC1F2B; }
    .progress-step.done::before, .progress-step.current::before { background: #DC1F2B; }
    .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; margin-bottom: 24px; align-items: start; }
    .section-card { position: relative; min-width: 0; padding: 24px; }
    .workflow-card { position: sticky; top: 16px; }
    .section-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #EEF0F3; }
    .panel-title { display: flex; align-items: center; gap: 11px; min-width: 0; }
    .panel-icon { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; flex: 0 0 36px; border-radius: 12px; background: #FDECEE; color: #DC1F2B; }
    .panel-icon svg { width: 18px; height: 18px; }
    .panel-title .label { margin-bottom: 3px; color: #6B7280; font-size: 11px; font-weight: 700; letter-spacing: .1em; }
    .panel-title h3 { margin: 0; color: #111827; font-size: 18px; font-weight: 700; }
    .panel-count { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; padding: 0 8px; border-radius: 999px; background: #FEF3C7; color: #92400E; font-size: 12px; font-weight: 700; }
    .workflow-card::before { content: ''; position: absolute; inset: 0 0 auto; height: 4px; border-radius: 20px 20px 0 0; background: #DC1F2B; }
    .workflow-card .section-head { margin-bottom: 20px; }
    .document-info { display: grid; grid-template-columns: 40% minmax(0, 1fr); overflow: hidden; border: 1px solid #EEF0F3; border-radius: 14px; }
    .document-info > div { min-width: 0; padding: 11px 13px; border-bottom: 1px solid #EEF0F3; font-size: 13px; line-height: 1.5; }
    .document-info > div:nth-last-child(-n+2) { border-bottom: 0; }
    .document-info > div:nth-child(odd) { color: #6B7280; background: #F9FAFB; }
    .document-info > div:nth-child(even) { color: #111827; font-weight: 700; overflow-wrap: anywhere; }
    .workflow-form { display: grid; gap: 20px; }
    .form-help { display: block; margin-top: 6px; color: #6B7280; font-size: 12px; }
    .field-error { display: block; margin-top: 5px; color: #9B1C1C; font-size: 12px; }
    .workflow-submit { width: 100%; min-height: 44px; border-radius: 999px; background: #DC1F2B; color: #FFFFFF; box-shadow: 0 8px 18px rgba(220,31,43,.18); }
    .workflow-submit:hover { background: #B0141F; }
    .confirmation-box { padding: 20px; border: 1px solid #F9CBA6; border-radius: 14px; background: #FFF8F2; }
    .confirmation-box h4 { margin: 0 0 8px; color: #111827; font-size: 16px; }
    .confirmation-box p { margin: 0 0 14px; color: #6B7280; font-size: 13px; line-height: 1.5; }
    .confirmation-box p strong { color: #111827; }
    .confirmation-box .button { width: 100%; min-height: 44px; border: 0; border-radius: 999px; background: #DC1F2B; color: #FFFFFF; box-shadow: 0 8px 18px rgba(220,31,43,.18); }
    .empty-workflow { padding: 18px; border-radius: 14px; background: #F9FAFB; color: #6B7280; font-size: 13px; line-height: 1.5; text-align: center; }
    .attachment-upload { margin-bottom: 16px; padding: 16px; border: 1px dashed #E5B4B8; border-radius: 14px; background: #FFF8F8; }
    .attachment-upload label { display: block; margin-bottom: 7px; color: #111827; font-size: 13px; font-weight: 600; }
    .attachment-upload-row { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
    .attachment-upload-row > div { flex: 1; min-width: 200px; }
    .attachment-upload input { background: #FFFFFF; }
    .attachment-upload .button { min-height: 40px; border-radius: 999px; box-shadow: none; }
    .attachment-help { display: block; margin-top: 7px; color: #6B7280; font-size: 12px; }
    .attachment-list { display: grid; gap: 10px; }
    .attachment-item { padding: 14px; border: 1px solid #EEF0F3; border-radius: 14px; background: #FFFFFF; }
    .attachment-item strong { color: #111827; font-size: 13px; overflow-wrap: anywhere; }
    .attachment-meta { margin: 5px 0 9px; color: #6B7280; font-size: 12px; }
    .attachment-item .button { padding: 7px 10px; border-radius: 999px; box-shadow: none; font-size: 12px; }
    .attachment-empty { padding: 20px; border-radius: 14px; background: #F9FAFB; color: #6B7280; font-size: 13px; text-align: center; }
    .history-list { display: grid; gap: 12px; }
    .history-entry-card { padding: 16px; border: 1px solid #EEF0F3; border-radius: 14px; background: #FFFFFF; }
    .history-entry-head { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #EEF0F3; }
    .history-entry-title { color: #111827; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .history-entry-date { margin-left: auto; color: #6B7280; font-size: 11px; text-align: right; }
    .history-route { display: flex; align-items: center; gap: 9px; min-width: 0; margin-bottom: 13px; padding: 10px 12px; border: 1px solid #F5DC8A; border-radius: 12px; background: #FFFBEB; color: #111827; font-size: 13px; font-weight: 600; line-height: 1.4; }
    .history-route span { min-width: 0; overflow-wrap: anywhere; }
    .history-route-arrow { flex: 0 0 auto; color: #92400E; font-size: 18px; }
    .history-details { display: grid; grid-template-columns: 100px minmax(0, 1fr); gap: 6px 12px; color: #111827; font-size: 13px; line-height: 1.5; }
    .history-details > div:nth-child(odd) { color: #6B7280; }
    .history-details > div:nth-child(even) { font-weight: 700; overflow-wrap: anywhere; }
    .detail-compact-select { position: relative; }
    .detail-native-select { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .detail-compact-trigger { display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 44px; padding: 10px 14px; border: 1px solid #E5E7EB; border-radius: 16px; background: #F6F7F9; color: #111827; font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
    .detail-compact-trigger:focus, .detail-compact-select.is-open .detail-compact-trigger { outline: none; border-color: #DC1F2B; box-shadow: 0 0 0 3px rgba(220,31,43,.12); }
    .detail-compact-trigger svg { width: 17px; height: 17px; color: #6B7280; }
    .detail-compact-options { position: absolute; top: calc(100% + 8px); left: 0; right: 0; z-index: 30; display: none; max-height: 280px; overflow-y: auto; padding: 6px; border: 1px solid #E5E7EB; border-radius: 16px; background: #FFFFFF; box-shadow: 0 12px 32px rgba(17,24,39,.14); }
    .detail-compact-select.is-open .detail-compact-options { display: grid; gap: 2px; }
    .detail-compact-option { padding: 12px 14px; border: 0; border-radius: 12px; background: transparent; color: #111827; font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
    .detail-compact-option:hover, .detail-compact-option.is-selected { background: #F6F7F9; }
    .detail-compact-option.is-selected { color: #DC1F2B; background: #FDECEE; }
    @media (max-width: 1023px) {
        .detail-grid { grid-template-columns: 1fr; }
        .workflow-card { position: static; }
    }
    @media (max-width: 640px) {
        .document-hero { padding: 22px 20px; }
        .document-hero-content { align-items: flex-start; flex-direction: column; }
        .hero-actions { justify-content: flex-start; width: 100%; }
        .hero-actions .button { flex: 1; text-align: center; }
        .progress-card, .section-card { padding: 20px; }
        .progress-summary { align-items: flex-start; flex-direction: column; }
        .progress-track { grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 14px; }
        .history-entry-date { width: 100%; margin-left: 0; text-align: left; }
        .history-details { grid-template-columns: 78px minmax(0, 1fr); }
    }
    /* Restore the previous Detail Dokumen visual treatment. */
    .document-detail .card { border-color: #dedede; box-shadow: 0 10px 28px rgba(10,40,74,.07); }
    .document-detail .document-hero { padding: 30px 32px; border: 0; border-radius: 20px; box-shadow: 0 16px 34px rgba(225,29,42,.10); }
    .document-detail .document-hero h2, .document-detail .document-hero p { color: #000000; }
    .document-detail .document-hero h2 { font-size: clamp(24px, 3vw, 34px); }
    .document-detail .hero-label { color: #D32F2F; }
    .document-detail .hero-actions .button { border-radius: 10px; background: #f0f0f0; color: #1f2937; }
    .document-detail .hero-actions .button:not(.secondary) { background: #D32F2F; color: #FFFFFF; }
    .document-detail .progress-card { margin-bottom: 20px; padding: 18px 24px; border-radius: 16px; }
    .document-detail .progress-aging { min-height: 0; padding: 7px 10px; border-color: var(--line); border-radius: 9px; color: var(--muted); background: #fafafa; font-weight: 600; }
    .document-detail .detail-grid { gap: 20px; margin-bottom: 18px; }
    .document-detail .section-card { padding: 24px; }
    .document-detail .section-card::before, .document-detail .section-card::after { display: none; }
    .document-detail .workflow-card { position: relative; top: auto; box-shadow: 0 12px 30px rgba(10,40,74,.09); }
    .document-detail .panel-icon { color: #D32F2F; background: #fff1f2; }
    .document-detail .panel-title .label { color: var(--muted); }
    .document-detail .panel-count { min-width: 28px; height: 28px; padding: 0; color: #374151; background: #F3F4F6; font-size: 13px; font-weight: 700; }
    .document-detail .document-info { margin-top: 0; border-color: #dedede; border-radius: 12px; }
    .document-detail .document-info > div { padding: 10px 12px; border-color: #dedede; }
    .document-detail .document-info > div:nth-child(odd) { background: #fafafa; }
    .document-detail .confirmation-box { border-color: #f1d49b; background: #fff8e8; }
    .document-detail .confirmation-box h4 { color: var(--warning); }
    .document-detail .workflow-form { gap: 16px; }
    .document-detail .workflow-submit { border-radius: 11px; background: #E11D2A; box-shadow: 0 10px 22px rgba(0,0,0,.16); }
    .document-detail .attachment-upload { border-color: #d4d4d4; background: #fafafa; }
    .document-detail .history-entry-card { border-color: #dedede; border-radius: 12px; box-shadow: 0 5px 14px rgba(0,0,0,.04); }
    .document-detail .history-route { border-color: #f1df80; background: #fffdf0; }
    .document-detail .detail-grid { align-items: stretch; }
    .document-detail .section-card { display: flex; flex-direction: column; gap: 20px; }
    .document-detail .section-card .section-head { flex: 0 0 auto; margin-bottom: 0; }
    .document-detail .document-info, .document-detail .attachment-list, .document-detail .history-list { flex: 1 1 auto; }
    .document-detail .attachment-list, .document-detail .history-list { min-height: 0; overflow-y: auto; }
    .document-detail .document-hero { border-left: 0; background: linear-gradient(115deg, #F8C6CB 0%, #FDECEE 55%, #FFFFFF 100%); }
    .document-detail .hero-document-line { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .document-detail .status-badge { display: inline-flex; align-items: center; gap: 7px; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; line-height: 1.2; }
    .document-detail .status-badge .status-dot { width: 7px; height: 7px; flex: 0 0 7px; border-radius: 50%; background: currentColor; }
    .document-detail .hero-status.on-process { color: #1D4ED8; background: #E8F0FE; border: 1px solid #BFD3FB; }
    .document-detail .hero-status.return { color: #C2410C; background: #FFF0E5; border: 1px solid #F9CBA6; }
    .document-detail .hero-status.waiting { color: #92400E; background: #FEF3C7; border: 1px solid #F5DC8A; }
    .document-detail .hero-status.approved { color: #15803D; background: #E6F6EC; border: 1px solid #BFE5CC; }
    .document-detail .hero-status.rejected { color: #9B1C1C; background: #FDECEC; border: 1px solid #F4C2C2; }
    .document-detail .hero-status.success { color: #6D28D9; background: #F0EAFD; border: 1px solid #D8C8F7; }
    .document-detail .hero-summary { display: flex; gap: 26px; flex-wrap: wrap; margin-top: 18px; }
    .document-detail .hero-summary-item { display: grid; gap: 3px; min-width: 110px; }
    .document-detail .hero-summary-item span { color: #9CA3AF; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .document-detail .hero-summary-item strong { color: #111827; font-size: 14px; overflow-wrap: anywhere; }
    .document-detail .progress-aging { background: #F3F4F6; color: #374151; border-color: #E5E7EB; }
    .document-detail .progress-card { background: #FFFFFF; border-top: 1px solid var(--line); }
    .document-detail .progress-aging svg { width: 16px; height: 16px; margin-right: 6px; }
    .document-detail .document-info { grid-template-columns: 38% minmax(0, 1fr); align-content: start; }
    .document-detail .document-info > div:not(.info-section-heading) { display: flex; align-items: center; min-height: 44px; padding: 12px 18px; background: #FFFFFF; border-bottom: 1px solid #F1F2F4; font-size: 14px; line-height: 1.4; }
    .document-detail .document-info > .info-label { color: #6B7280; font-weight: 500; }
    .document-detail .document-info > .info-value { color: #111827; font-weight: 700; overflow-wrap: anywhere; }
    .document-detail .document-info > div:nth-last-child(-n+2) { border-bottom: 1px solid #F1F2F4; }
    .document-detail .info-section-heading { grid-column: 1 / -1; min-height: 0 !important; padding: 10px 18px !important; background: #F9FAFB !important; color: #6B7280 !important; border-bottom: 1px solid #EEF0F3 !important; font-size: 11px !important; font-weight: 700 !important; letter-spacing: .08em; text-transform: uppercase; }
    .document-detail .info-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .document-detail .info-pill.yes { color: #C2410C; background: #FFF0E5; }
    .document-detail .info-pill.no { color: #4B5563; background: #F3F4F6; }
    .document-detail .workflow-card { border-top: 4px solid #DC1F2B; }
    .document-detail .workflow-card::before { display: none; }
    .document-detail .workflow-card .workflow-form { flex: 1; }
    .document-detail .workflow-form { display: flex; flex-direction: column; gap: 20px; }
    .document-detail .workflow-form .form-section { display: flex; flex-direction: column; gap: 8px; min-width: 0; padding: 0; border: 0; border-radius: 0; background: transparent; }
    .document-detail .workflow-form .form-section label { margin: 0; color: #111827; font-size: 13px; font-weight: 700; }
    .document-detail .workflow-form .form-section .required { color: #DC1F2B; }
    .document-detail .workflow-form .form-section input,
    .document-detail .workflow-form .form-section textarea { width: 100%; min-height: 48px; padding: 0 16px; border: 1px solid #E5E7EB; border-radius: 16px; background: #F6F7F9; color: #111827; font-size: 14px; }
    .document-detail .workflow-form .form-section textarea { min-height: 100px; padding-top: 12px; padding-bottom: 12px; resize: vertical; }
    .document-detail .workflow-form .form-section input[readonly] { background: #ECEEF1; color: #374151; }
    .document-detail .workflow-form .form-section input:focus,
    .document-detail .workflow-form .form-section textarea:focus { outline: none; border-color: #DC1F2B; box-shadow: 0 0 0 3px rgba(220,31,43,.12); }
    .document-detail .workflow-form .form-help { margin: 0; color: #6B7280; font-size: 12px; }
    .document-detail .workflow-form .field-error { margin: 0; color: #9B1C1C; font-size: 12px; }
    .document-detail .workflow-form .detail-compact-trigger { min-height: 48px; border-radius: 16px; }
    .document-detail .workflow-form .detail-compact-select.is-open .detail-compact-trigger,
    .document-detail .workflow-form .detail-compact-trigger:focus { border-color: #DC1F2B; box-shadow: 0 0 0 3px rgba(220,31,43,.12); }
    .document-detail .workflow-form #noteGroup.is-visible { display: flex !important; flex: 1; flex-direction: column; }
    .document-detail .workflow-form #noteGroup.is-visible textarea { flex: 1; }
    .document-detail .workflow-submit { margin-top: auto; min-height: 48px; background: #DC1F2B; border-radius: 999px; box-shadow: 0 8px 18px rgba(220,31,43,.18); }
    .document-detail .confirmation-box { display: flex; flex: 1; flex-direction: column; align-items: center; justify-content: center; padding: 28px 20px; border: 0; background: transparent; text-align: center; }
    .document-detail .confirmation-box::before { content: '⇩'; display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; margin-bottom: 24px; border-radius: 50%; background: #FDECEE; color: #DC1F2B; font-size: 32px; font-weight: 800; box-shadow: 0 0 0 10px #FFF5F5; }
    .document-detail .confirmation-box h4 { margin-bottom: 8px; color: #111827; font-size: 22px; }
    .document-detail .confirmation-box > p { max-width: 340px; margin: 0 0 14px; color: #6B7280; }
    .document-detail .confirmation-box .confirm-route { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); align-items: start; width: min(440px, 100%); gap: 10px; margin: 4px 0 14px; padding: 18px 20px; border-radius: 18px; background: #F6F7F9; text-align: left; }
    .document-detail .confirm-route small { display: block; margin-bottom: 4px; color: #9CA3AF; font-size: 10px; font-weight: 700; letter-spacing: .08em; }
    .document-detail .confirm-route strong { color: #374151; font-size: 13px; overflow-wrap: anywhere; }
    .document-detail .confirm-route > div:last-child strong { color: #B0141F; }
    .document-detail .confirm-route .arrow { align-self: start; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-top: 17px; border-radius: 50%; background: #FFFFFF; color: #DC1F2B; font-weight: 800; box-shadow: 0 1px 4px rgba(17,24,39,.12); }
    .document-detail .confirm-reason { width: min(440px, 100%); margin: 0 0 16px; padding: 6px 12px; border-radius: 999px; background: #FFF0E5; color: #C2410C; font-size: 13px; }
    .document-detail .confirm-note { width: min(440px, 100%); margin: 0 0 16px; padding: 6px 12px; border: 1px solid #E5E7EB; border-radius: 999px; background: #F3F4F6; color: #374151; font-size: 13px; }
    .document-detail .confirmation-box form { display: flex; align-self: stretch; justify-content: center; width: 100%; }
    .document-detail .confirmation-box .button { width: 100%; max-width: 440px; min-height: 52px; font-size: 15px; font-weight: 700; }
    .document-detail .attachment-upload { border: 1px solid #E5E7EB; background: #FFFFFF; }
    .document-detail .attachment-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 130px; border: 1.5px dashed #D1D5DB; background: #F9FAFB; }
    .document-detail .history-list { gap: 16px; }
    .document-detail .history-list { position: relative; padding-left: 28px; }
    .document-detail .history-list::before { display: none; }
    .document-detail .history-entry-card { position: relative; border-color: #EEF0F3; border-radius: 16px; }
    .document-detail .history-entry-card::before { content: ''; position: absolute; top: 20px; left: -28px; width: 14px; height: 14px; border: 3px solid #DC1F2B; border-radius: 50%; background: #FFFFFF; }
    .document-detail .history-entry-card:not(:last-child)::after { content: ''; position: absolute; top: 37px; bottom: -16px; left: -21px; width: 2px; background: #E5E7EB; }
    .document-detail .history-route { border-color: #EEF0F3; background: #F9FAFB; border-radius: 12px; padding: 10px 14px; font-size: 13px; font-weight: 600; }
    .document-detail .history-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .document-detail .history-detail-item { display: grid; gap: 3px; min-width: 0; }
    .document-detail .history-detail-item .history-detail-label { color: #9CA3AF; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .document-detail .history-detail-item .history-detail-value { color: #111827; font-size: 13px; font-weight: 700; overflow-wrap: anywhere; }
    .document-detail .history-details > div:nth-child(odd), .document-detail .history-details > div:nth-child(even) { color: inherit; font-weight: inherit; }
    .document-detail .attachment-item { padding: 14px 16px; border-color: #EEF0F3; }
    .document-detail .attachment-item strong { font-size: 14px; }
    .document-detail .attachment-item .button.secondary { background: #F3F4F6; color: #374151; border-radius: 999px; }
    .document-detail .attachment-item .button.danger { background: #FDECEE; color: #9B1C1C; border-radius: 999px; }
    .document-detail .attachment-item .button.danger:hover { background: #FBD5D5; }
    .document-detail .progress-step.done { color: #4B5563; }
    .document-detail .progress-step.current { color: #DC1F2B; font-weight: 800; }
    .document-detail .attachment-empty::before { content: '▧'; display: block; margin-bottom: 6px; color: #9CA3AF; font-size: 28px; }
    @media (max-width: 1023px) { .document-detail .detail-grid { grid-template-columns: 1fr; } .document-detail .workflow-card { position: relative; } }
</style>

<div class="document-detail">
    <div class="card document-hero">
        <div class="document-hero-content">
            <div>
                <div class="hero-label">DOKUMEN</div>
                <div class="hero-document-line"><h2><?php echo e($document->document_number); ?></h2><span class="hero-status status-badge <?php echo e($badgeClass); ?>"><span class="status-dot" aria-hidden="true"></span><?php echo e($document->latestWorkflow?->formatted_status ?? '-'); ?></span></div>
                <p><?php echo e($document->subject); ?></p>
                <div class="hero-summary">
                    <div class="hero-summary-item"><span>Jenis</span><strong><?php echo e($document->documentType?->name ?? '-'); ?></strong></div>
                    <div class="hero-summary-item"><span>Unit Saat Ini</span><strong><?php echo e($document->currentUnit()?->name ?? '-'); ?></strong></div>
                    <div class="hero-summary-item"><span>PIC Saat Ini</span><strong><?php echo e($document->currentPic() ?? '-'); ?></strong></div>
                </div>
            </div>
            <div class="hero-actions">
                <a class="button secondary" href="<?php echo e(route('documents.index')); ?>">Kembali</a>
                <a class="button" href="<?php echo e(route('documents.edit', $document)); ?>">Edit dokumen</a>
            </div>
        </div>
    </div>

    <?php
        $progressIndex = match ($currentStatus) {
            \App\Models\DocumentWorkflow::STATUS_COMPLETED => 4,
            \App\Models\DocumentWorkflow::STATUS_APPROVED,
            \App\Models\DocumentWorkflow::STATUS_REJECTED => 3,
            \App\Models\DocumentWorkflow::STATUS_ON_PROCESS,
            \App\Models\DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI => 2,
            default => 1,
        };
    ?>
    <div class="card progress-card">
        <div class="progress-summary">
            <div class="progress-status">
                <div class="label">STATUS PROSES</div>
                <span class="badge status-badge <?php echo e($badgeClass); ?>"><span class="status-dot" aria-hidden="true"></span><?php echo e($document->latestWorkflow?->formatted_status ?? '-'); ?></span>
            </div>
            <span class="progress-aging <?php echo e($badgeClass); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="8"></circle><path d="M12 8v5l3 2"></path></svg><?php echo e($document->currentAging()); ?></span>
        </div>
        <div class="progress-track" aria-label="Progress dokumen">
            <?php $__currentLoopData = ['Dibuat', 'Diproses', 'Disetujui', 'Selesai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stepIndex => $stepLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="progress-step <?php echo e($stepIndex + 1 <= $progressIndex ? 'done' : ''); ?> <?php echo e($stepIndex + 1 === $progressIndex ? 'current' : ''); ?>"><?php echo e($stepLabel); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="detail-grid">
        <div class="card section-card">
            <div class="section-head">
                <div class="panel-title"><span class="panel-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h9l3 3v15H6z"></path><path d="M14 3v4h4M9 12h6M9 16h6"></path></svg></span><div><div class="label">RINGKASAN</div><h3>Informasi Dokumen</h3></div></div>
            </div>
            <div class="document-info">
                <div class="info-section-heading">DOKUMEN</div>
                <div class="info-label">Tanggal Masuk</div><div class="info-value"><?php echo e($document->received_at?->format('d M Y')); ?></div>
                <div class="info-label">Jenis Dokumen</div><div class="info-value"><?php echo e($document->documentType?->name); ?></div>
                <div class="info-label">Unit Pengirim</div><div class="info-value"><?php echo e($document->senderUnit?->name); ?></div>
                <div class="info-label">PIC Awal</div><div class="info-value"><?php echo e($document->initial_pic); ?></div>
                <div class="info-section-heading">POSISI SAAT INI</div>
                <div class="info-label">Unit Saat Ini</div><div class="info-value"><?php echo e($document->currentUnit()?->name ?? '-'); ?></div>
                <div class="info-label">PIC Saat Ini</div><div class="info-value"><?php echo e($document->currentPic() ?? '-'); ?></div>
                <div class="info-label">Unit Asal</div><div class="info-value"><?php echo e($document->latestWorkflow?->fromUnit?->name ?? '-'); ?></div>
                <div class="info-label">Tgl Diterima PIC</div><div class="info-value">
                    <?php $receivedAt = $document->latestWorkflow?->received_at; $isZeroTime = $receivedAt && $receivedAt->format('H:i:s') === '00:00:00'; ?>
                    <?php echo e((!$receivedAt || $isZeroTime) ? 'Belum dikonfirmasi' : $receivedAt->format('d M Y H:i')); ?>

                </div>
                <div class="info-section-heading">AGING &amp; RIWAYAT</div>
                <div class="info-label">Current Aging</div><div class="info-value"><?php echo e($document->currentAging()); ?></div>
                <div class="info-label">Total Aging</div><div class="info-value"><?php echo e($document->totalAging()); ?></div>
                <div class="info-label">Pernah Return</div><div class="info-value"><span class="info-pill <?php echo e($document->hasReturn() ? 'yes' : 'no'); ?>"><?php echo e($document->hasReturn() ? 'Ya' : 'Tidak'); ?></span></div>
                <div class="info-label">Pernah Reject</div><div class="info-value"><span class="info-pill <?php echo e($document->hasReject() ? 'yes' : 'no'); ?>"><?php echo e($document->hasReject() ? 'Ya' : 'Tidak'); ?></span></div>
                <div class="info-label">Catatan Awal</div><div class="info-value"><?php echo e($document->notes ?: '-'); ?></div>
            </div>
        </div>

        <div class="card section-card workflow-card">
            <div class="section-head">
                <div class="panel-title"><span class="panel-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h13"></path><path d="m14 7 5 5-5 5"></path><path d="M5 5v14"></path></svg></span><div><h3>Aksi Dokumen</h3></div></div>
            </div>

            <?php if($isWaitingConfirmation): ?>
                <div class="confirmation-box">
                    <h4>Terima dokumen ini?</h4>
                    <p>Dokumen ini dikirim ke unit Anda. Konfirmasi untuk mulai memprosesnya.</p>
                    <div class="confirm-route"><div><small>DARI</small><strong><?php echo e($currentWorkflow->fromUnit?->name); ?></strong></div><span class="arrow" aria-hidden="true">→</span><div><small>KE</small><strong><?php echo e($currentWorkflow->toUnit?->name); ?></strong></div></div>
                    <?php if($confirmationNote && $isReturnedConfirmation): ?>
                        <div class="confirm-reason">↩ Dikembalikan: <?php echo e($confirmationNote); ?></div>
                    <?php elseif($confirmationNote): ?>
                        <div class="confirm-note">▤ Catatan: <?php echo e($confirmationNote); ?></div>
                    <?php endif; ?>
                    <form action="<?php echo e(route('documents.workflows.confirm-receipt', $document)); ?>" method="POST" class="confirm-receipt-form" onsubmit="return confirm('Yakin dokumen sudah diterima dan siap diproses?')">
                        <?php echo csrf_field(); ?>
                        <button class="button" type="submit">Konfirmasi Penerimaan</button>
                    </form>
                </div>
            <?php elseif(!empty($allowedActions)): ?>
                <form action="<?php echo e(route('documents.workflows.store', $document)); ?>" method="POST" id="workflowForm" class="workflow-form">
                    <?php echo csrf_field(); ?>
                    <div class="form-section">
                        <label for="action">Aksi</label>
                        <div class="detail-compact-select" data-detail-select data-input-id="action" data-label-id="actionLabel">
                            <select id="action" name="action" class="detail-native-select" required aria-hidden="true" tabindex="-1">
                                <option value="">-- Pilih Aksi --</option>
                                <?php if(in_array('disposisi', $allowedActions)): ?><option value="disposisi" <?php if(old('action') === 'disposisi'): echo 'selected'; endif; ?>>Disposisi</option><?php endif; ?>
                                <?php if(in_array('approved', $allowedActions)): ?><option value="approved" <?php if(old('action') === 'approved'): echo 'selected'; endif; ?>>Approved</option><?php endif; ?>
                                <?php if(in_array('return', $allowedActions)): ?><option value="return" <?php if(old('action') === 'return'): echo 'selected'; endif; ?>>Return</option><?php endif; ?>
                                <?php if(in_array('rejected', $allowedActions)): ?><option value="rejected" <?php if(old('action') === 'rejected'): echo 'selected'; endif; ?>>Reject</option><?php endif; ?>
                                <?php if(in_array('completed', $allowedActions)): ?><option value="completed" <?php if(old('action') === 'completed'): echo 'selected'; endif; ?>>Selesai</option><?php endif; ?>
                            </select>
                            <button type="button" class="detail-compact-trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="actionOptions" aria-labelledby="actionLabel"><span id="actionLabel"><?php echo e(old('action') ? ucfirst(old('action')) : '-- Pilih Aksi --'); ?></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg></button>
                            <div class="detail-compact-options" id="actionOptions" role="listbox">
                                <button type="button" class="detail-compact-option <?php echo e(old('action') ? '' : 'is-selected'); ?>" role="option" aria-selected="<?php echo e(old('action') ? 'false' : 'true'); ?>" data-value="">-- Pilih Aksi --</button>
                                <?php $__currentLoopData = $allowedActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $actionLabel = match($action) { 'disposisi' => 'Disposisi', 'approved' => 'Approved', 'return' => 'Return', 'rejected' => 'Reject', 'completed' => 'Selesai', default => $action }; ?>
                                    <button type="button" class="detail-compact-option <?php echo e(old('action') === $action ? 'is-selected' : ''); ?>" role="option" aria-selected="<?php echo e(old('action') === $action ? 'true' : 'false'); ?>" data-value="<?php echo e($action); ?>"><?php echo e($actionLabel); ?></button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php $__errorArgs = ['action'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-section">
                        <label for="from_unit">Dari Unit</label>
                        <input type="text" id="from_unit" value="<?php echo e($currentWorkflow?->fromUnit?->name ?? ($currentWorkflow?->toUnit?->name ?? '-')); ?>" readonly>
                    </div>

                    <div id="toUnitGroup" class="form-section" style="display: none;">
                        <label for="to_unit_id">Unit Tujuan <span class="required">*</span></label>
                        <div class="detail-compact-select" data-detail-select data-input-id="to_unit_id" data-label-id="toUnitLabel">
                            <select id="to_unit_id" name="to_unit_id" class="detail-native-select" aria-hidden="true" tabindex="-1">
                                <option value="">Pilih unit tujuan</option>
                                <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($unit->id); ?>" <?php if(old('to_unit_id') == $unit->id): echo 'selected'; endif; ?>><?php echo e($unit->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="button" class="detail-compact-trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="toUnitOptions" aria-labelledby="toUnitLabel"><span id="toUnitLabel"><?php echo e(old('to_unit_id') ? $units->firstWhere('id', old('to_unit_id'))?->name : 'Pilih unit tujuan'); ?></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg></button>
                            <div class="detail-compact-options" id="toUnitOptions" role="listbox">
                                <button type="button" class="detail-compact-option <?php echo e(old('to_unit_id') ? '' : 'is-selected'); ?>" role="option" aria-selected="<?php echo e(old('to_unit_id') ? 'false' : 'true'); ?>" data-value="">Pilih unit tujuan</button>
                                <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><button type="button" class="detail-compact-option <?php echo e(old('to_unit_id') == $unit->id ? 'is-selected' : ''); ?>" role="option" aria-selected="<?php echo e(old('to_unit_id') == $unit->id ? 'true' : 'false'); ?>" data-value="<?php echo e($unit->id); ?>"><?php echo e($unit->name); ?></button><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <small class="form-help" id="toUnitHint">Wajib diisi</small>
                        <?php $__errorArgs = ['to_unit_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div id="picGroup" class="form-section" style="display: none;">
                        <label for="pic">PIC Tujuan <span class="required">*</span></label>
                        <input id="pic" name="pic" value="<?php echo e(old('pic', $currentWorkflow?->pic)); ?>">
                        <?php $__errorArgs = ['pic'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div id="noteGroup" class="form-section" style="display: none;">
                        <label for="note">Catatan</label>
                        <textarea id="note" name="note"><?php echo e(old('note')); ?></textarea>
                        <small class="form-help" id="noteHint">Opsional</small>
                        <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div id="alasanGroup" class="form-section" style="display: none;">
                        <label for="alasan">Alasan <span class="required">*</span></label>
                        <textarea id="alasan" name="alasan" placeholder="Masukkan alasan..."><?php echo e(old('alasan')); ?></textarea>
                        <small class="form-help" id="alasanHint">Wajib diisi</small>
                        <?php $__errorArgs = ['alasan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <button class="button workflow-submit" type="submit" id="submitBtn">Simpan Workflow</button>
                </form>
            <?php else: ?>
                <div class="empty-workflow">
                    <?php if($isCompleted): ?><strong>Dokumen telah diselesaikan (Done).</strong><?php else: ?> Tidak ada aksi yang tersedia untuk status <strong><?php echo e($currentWorkflow?->formatted_status); ?></strong>. <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-grid">
        <div class="card section-card">
            <div class="section-head">
                <div class="panel-title"><span class="panel-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21.4 11.6 12 21a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"></path></svg></span><div><div class="label">FILE DOKUMEN</div><h3>Lampiran</h3></div></div>
                <span class="panel-count"><?php echo e($document->attachments->count()); ?></span>
            </div>
            <form action="<?php echo e(route('attachments.store', $document)); ?>" method="POST" enctype="multipart/form-data" class="attachment-upload">
                <?php echo csrf_field(); ?>
                <div class="attachment-upload-row"><div><label for="attachments">Tambah File</label><input id="attachments" type="file" name="attachments[]" multiple></div><button class="button secondary" type="submit">Upload</button></div>
                <small class="attachment-help">Multi-file, maks. 10 MB/file. Format: PDF, JPG, PNG, Excel, dll.</small>
            </form>
            <div class="attachment-list">
                <?php $__empty_1 = true; $__currentLoopData = $document->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="attachment-item">
                        <strong><?php echo e($attachment->file_name); ?></strong>
                        <div class="attachment-meta"><?php echo e($attachment->file_type ?: 'Tipe tidak diketahui'); ?> | <?php echo e(number_format($attachment->file_size / 1024, 2)); ?> KB</div>
                        <a href="<?php echo e(route('attachments.preview', ['attachment' => $attachment, 'filename' => $attachment->file_name])); ?>" target="_blank" class="button secondary small">Preview</a>
                        <a href="<?php echo e(route('attachments.download', $attachment)); ?>" class="button secondary small">Download</a>
                        <form action="<?php echo e(route('attachments.delete', $attachment)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus file ini? File yang dihapus tidak dapat dikembalikan.');"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit" class="button danger small">Hapus</button></form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="attachment-empty">Belum ada attachment.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card section-card">
            <div class="section-head">
                <div class="panel-title"><span class="panel-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></span><div><div class="label">AKTIVITAS DOKUMEN</div><h3>Riwayat Workflow</h3></div></div>
                <span class="panel-count"><?php echo e($document->workflows->count()); ?></span>
            </div>
            <div class="history-list">
                <?php $__currentLoopData = $document->workflows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $workflow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isFirst = $index === 0;
                        $isReceived = $workflow->direction === \App\Models\DocumentWorkflow::DIRECTION_RECEIVED;
                        $isSent = $workflow->direction === \App\Models\DocumentWorkflow::DIRECTION_SENT;
                        $directionLabel = match($workflow->direction) {
                            \App\Models\DocumentWorkflow::DIRECTION_CREATED => 'Dibuat',
                            \App\Models\DocumentWorkflow::DIRECTION_SENT => 'Disposisi (Dikirim)',
                            \App\Models\DocumentWorkflow::DIRECTION_RECEIVED => 'Konfirmasi Penerimaan',
                            \App\Models\DocumentWorkflow::DIRECTION_RETURNED => 'Return',
                            \App\Models\DocumentWorkflow::DIRECTION_APPROVED => 'Approved',
                            \App\Models\DocumentWorkflow::DIRECTION_REJECTED => 'Rejected',
                            default => $workflow->direction,
                        };
                        $workflowReceivedAt = $workflow->received_at;
                        $workflowZeroTime = $workflowReceivedAt && $workflowReceivedAt->format('H:i:s') === '00:00:00';
                    ?>
                    <div class="history-entry-card">
                        <div class="history-entry-head">
                            <span class="history-entry-title"><?php echo e($index + 1); ?>. <?php echo e($directionLabel); ?></span>
                            <?php if($workflow->status): ?><span class="badge status-badge <?php echo e($workflow->badge_class); ?>"><span class="status-dot" aria-hidden="true"></span><?php echo e($workflow->formatted_status); ?></span><?php endif; ?>
                            <span class="history-entry-date">
                                <?php if($isSent && $workflowReceivedAt && !$workflowZeroTime): ?> Dikirim: <?php echo e($workflow->created_at?->format('d M Y H:i')); ?> | Diterima: <?php echo e($workflowReceivedAt->format('d M Y H:i')); ?>

                                <?php elseif($isReceived && $workflowReceivedAt && !$workflowZeroTime): ?> <?php echo e($workflowReceivedAt->format('d M Y H:i')); ?>

                                <?php else: ?> <?php echo e($workflow->created_at?->format('d M Y H:i')); ?> <?php endif; ?>
                            </span>
                        </div>
                        <div class="history-route"><span><?php echo e($workflow->fromUnit?->name ?? ($isFirst ? 'Dokumen dibuat' : '-')); ?></span><span class="history-route-arrow" aria-hidden="true">→</span><span><?php echo e($workflow->toUnit?->name ?? '-'); ?></span></div>
                        <div class="history-details">
                            <div class="history-detail-item"><span class="history-detail-label">PIC Pengirim</span><span class="history-detail-value"><?php echo e($workflow->sender_pic ?? ($workflow->direction === \App\Models\DocumentWorkflow::DIRECTION_CREATED ? $workflow->pic : '-')); ?></span></div>
                            <div class="history-detail-item"><span class="history-detail-label">PIC Penerima</span><span class="history-detail-value"><?php echo e($workflow->receiver_pic ?? (in_array($workflow->direction, [\App\Models\DocumentWorkflow::DIRECTION_SENT, \App\Models\DocumentWorkflow::DIRECTION_RECEIVED], true) ? $workflow->pic : '-')); ?></span></div>
                            <?php if($isSent || $isReceived): ?>
                                <div class="history-detail-item"><span class="history-detail-label">Dikirim</span><span class="history-detail-value"><?php echo e($workflow->created_at?->format('d M Y H:i')); ?></span></div>
                                <div class="history-detail-item"><span class="history-detail-label">Diterima</span><span class="history-detail-value"><?php echo e((!$workflowReceivedAt || $workflowZeroTime) ? 'Belum dikonfirmasi' : $workflowReceivedAt->format('d M Y H:i')); ?></span></div>
                            <?php else: ?>
                                <div class="history-detail-item"><span class="history-detail-label">Tanggal</span><span class="history-detail-value"><?php echo e((!$workflowReceivedAt || $workflowZeroTime) ? $workflow->created_at?->format('d M Y H:i') : $workflowReceivedAt->format('d M Y H:i')); ?></span></div>
                            <?php endif; ?>
                            <?php if($workflow->note): ?><div class="history-detail-item"><span class="history-detail-label">Catatan</span><span class="history-detail-value"><?php echo e($workflow->note); ?></span></div><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (() => {
        const selects = [...document.querySelectorAll('[data-detail-select]')];
        const closeSelect = (select, returnFocus = false) => {
            select.classList.remove('is-open');
            const trigger = select.querySelector('.detail-compact-trigger');
            trigger?.setAttribute('aria-expanded', 'false');
            if (returnFocus) trigger?.focus();
        };
        const openSelect = (select, focusOption = false) => {
            selects.forEach((item) => { if (item !== select) closeSelect(item); });
            select.classList.add('is-open');
            const trigger = select.querySelector('.detail-compact-trigger');
            trigger?.setAttribute('aria-expanded', 'true');
            if (focusOption) {
                const selected = select.querySelector('.detail-compact-option.is-selected') || select.querySelector('.detail-compact-option');
                selected?.focus();
            }
        };

        selects.forEach((select) => {
            const nativeSelect = select.querySelector('select');
            const trigger = select.querySelector('.detail-compact-trigger');
            const options = [...select.querySelectorAll('.detail-compact-option')];
            const label = select.querySelector('[data-label], [id$="Label"]');
            const sync = () => {
                const selected = options.find((option) => option.dataset.value === nativeSelect.value);
                if (!selected) return;
                options.forEach((option) => {
                    const isSelected = option === selected;
                    option.classList.toggle('is-selected', isSelected);
                    option.setAttribute('aria-selected', String(isSelected));
                });
                if (label) label.textContent = selected.textContent.trim();
            };
            options.forEach((option, index) => {
                option.tabIndex = -1;
                option.addEventListener('click', () => {
                    nativeSelect.value = option.dataset.value;
                    nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    sync();
                    closeSelect(select);
                    trigger.focus();
                });
                option.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        options[(index + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length].focus();
                    } else if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        option.click();
                    } else if (event.key === 'Escape') {
                        event.preventDefault();
                        closeSelect(select, true);
                    } else if (event.key === 'Tab') {
                        closeSelect(select);
                    }
                });
            });
            nativeSelect.addEventListener('change', sync);
            trigger.addEventListener('click', () => select.classList.contains('is-open') ? closeSelect(select) : openSelect(select, true));
            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openSelect(select, true);
                } else if (event.key === 'Escape') closeSelect(select);
                else if (event.key === 'Tab') closeSelect(select);
            });
            sync();
        });

        document.addEventListener('click', (event) => {
            selects.forEach((select) => { if (!select.contains(event.target)) closeSelect(select); });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') selects.forEach((select) => closeSelect(select));
        });

        const actionSelect = document.getElementById('action');
        const toUnitSelect = document.getElementById('to_unit_id');
        const toUnitGroup = document.getElementById('toUnitGroup');
        const toUnitHint = document.getElementById('toUnitHint');
        const picGroup = document.getElementById('picGroup');
        const picInput = document.getElementById('pic');
        const noteGroup = document.getElementById('noteGroup');
        const noteTextarea = document.getElementById('note');
        const noteHint = document.getElementById('noteHint');
        const alasanGroup = document.getElementById('alasanGroup');
        const alasanTextarea = document.getElementById('alasan');
        const alasanHint = document.getElementById('alasanHint');
        const workflowForm = document.getElementById('workflowForm');
        const setCustomValue = (nativeSelect, value) => {
            if (!nativeSelect) return;
            nativeSelect.value = String(value);
            nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        };
        const updateForm = () => {
            if (!actionSelect) return;
            const action = actionSelect.value;
            toUnitGroup.style.display = 'none';
            toUnitSelect.required = false;
            picGroup.style.display = 'none';
            picInput.required = false;
            noteGroup.style.display = 'block';
            noteGroup.classList.add('is-visible');
            noteTextarea.required = false;
            noteHint.textContent = 'Opsional';
            alasanGroup.style.display = 'none';
            alasanTextarea.required = false;

            if (action === 'disposisi') {
                toUnitGroup.style.display = 'block'; toUnitSelect.required = true; toUnitHint.textContent = 'Wajib diisi';
                picGroup.style.display = 'block'; picInput.required = true;
            } else if (action === 'return') {
                toUnitGroup.style.display = 'block'; toUnitSelect.required = true; toUnitHint.textContent = 'Otomatis ke Unit Pembuat';
                setCustomValue(toUnitSelect, <?php echo e($document->sender_unit_id); ?>);
                picGroup.style.display = 'block'; picInput.required = true;
                alasanGroup.style.display = 'block'; alasanTextarea.required = true; alasanHint.textContent = 'Wajib: alasan return'; alasanTextarea.placeholder = 'Contoh: Revisi nilai budget';
                noteGroup.style.display = 'none';
                noteGroup.classList.remove('is-visible');
            } else if (action === 'approved') {
                noteTextarea.placeholder = 'Contoh: Dokumen telah disetujui';
            } else if (action === 'rejected') {
                noteGroup.style.display = 'none'; noteGroup.classList.remove('is-visible'); alasanGroup.style.display = 'block'; alasanTextarea.required = true; alasanHint.textContent = 'Wajib: alasan reject'; alasanTextarea.placeholder = 'Contoh: Dokumen tidak memenuhi ketentuan';
            } else if (action === 'completed') {
                noteTextarea.placeholder = 'Contoh: Dokumen telah diarsipkan / Proses selesai';
            }
        };
        actionSelect?.addEventListener('change', updateForm);
        updateForm();

        const confirmMessages = { disposisi: 'Yakin ingin mendisposisikan dokumen ke unit lain? Unit tujuan perlu konfirmasi penerimaan.', return: 'Yakin ingin mengembalikan dokumen ke unit pembuat?', approved: 'Yakin ingin menyetujui dokumen ini? Status akan menjadi final.', rejected: 'Yakin ingin menolak dokumen ini? Status akan menjadi final.', completed: 'Yakin ingin menandai dokumen ini sebagai Selesai (Done)?' };
        workflowForm?.addEventListener('submit', (event) => {
            const message = confirmMessages[actionSelect?.value];
            if (message && !confirm(message)) { event.preventDefault(); return; }
            const submit = workflowForm.querySelector('button[type="submit"]');
            if (submit) { submit.disabled = true; submit.setAttribute('aria-disabled', 'true'); }
        });
        document.querySelectorAll('.confirm-receipt-form').forEach((form) => form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) return;
            const submit = form.querySelector('button[type="submit"]');
            if (submit) { submit.disabled = true; submit.setAttribute('aria-disabled', 'true'); }
        }));
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Detail Dokumen'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\monitoring-dokumen\resources\views\documents\show.blade.php ENDPATH**/ ?>