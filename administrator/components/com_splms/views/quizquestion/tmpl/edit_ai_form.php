<?php
defined ('_JEXEC') or die('Resticted Aceess');
?>
<div class="form-horizontal">
    <div class="control-group mb-3">
        <label class="control-label form-label" for="ai_topic">Topic</label>
        <div class="controls">
            <input type="text" id="ai_topic" placeholder="e.g. PHP Basics" class="form-control input-xlarge" style="width: 100%;">
        </div>
    </div>
    <div class="control-group mb-3">
        <label class="control-label form-label" for="ai_description">Additional Context / Description</label>
        <div class="controls">
            <textarea id="ai_description" rows="3" placeholder="Paste any extra text, rules, or specific instructions here..." class="form-control" style="width: 100%;"></textarea>
        </div>
    </div>
    <div class="control-group mb-3">
        <label class="control-label form-label" for="ai_difficulty">Difficulty</label>
        <div class="controls">
            <select id="ai_difficulty" class="form-select" style="width: 100%;">
                <option value="Easy">Easy</option>
                <option value="Medium" selected>Medium</option>
                <option value="Hard">Hard</option>
            </select>
        </div>
    </div>
    <div class="control-group mb-3">
        <label class="control-label form-label" for="ai_count">Number of Questions</label>
        <div class="controls">
            <input type="number" id="ai_count" value="5" min="1" max="20" class="form-control" style="width: 100%;">
        </div>
    </div>
    <div class="control-group mb-3">
        <label class="control-label form-label" for="ai_duration">Duration (Minutes)</label>
        <div class="controls">
            <input type="number" id="ai_duration" value="10" min="1" class="form-control" style="width: 100%;">
            <small class="text-muted">Will auto-fill the main duration field.</small>
        </div>
    </div>
    <div class="control-group mb-3">
        <label class="control-label form-label" for="ai_file">Source Document (PDF/DOCX)</label>
        <div class="controls">
            <input type="file" id="ai_file" accept=".pdf,.docx" class="form-control" style="width: 100%;">
            <small class="text-muted">Optional. Overrides 'Topic' if provided.</small>
        </div>
    </div>
</div>
<div id="ai_status" style="display:none;" class="alert alert-info mt-3">Generating... please wait.</div>
