{{-- resources/views/admin/form-builder.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="app-content">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">{{$title}}</h4>
        </div>

        <div class="form-builder-container" x-data="formBuilder()" x-init="init()">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#form-editor" role="tab">
                                Form Editor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#settings" role="tab">
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="form-editor" role="tabpanel">
                            <div class="form-group">
                                <label for="form-title">Form Title <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="form-title" 
                                       x-model="formTitle" 
                                       @input="updateTitle"
                                       maxlength="200"
                                       placeholder="Enter form title">
                                <small class="text-muted">
                                    <span x-text="formTitle.length"></span>/200 characters
                                </small>
                            </div>

                            <div class="form-group mb-4">
                                <label for="submit-url">Form Submission URL</label>
                                <input type="url" 
                                       class="form-control" 
                                       id="submit-url" 
                                       x-model="submitUrl"
                                       placeholder="https://example.com/submit">
                                <small class="text-muted">Where the form data will be submitted</small>
                            </div>

                            <div class="row">
                                <div class="col-lg-8 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Form Canvas</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="drop-canvas"
                                                 @dragover.prevent="onDragOver"
                                                 @dragleave.prevent="onDragLeave"
                                                 @drop.prevent="onDrop"
                                                 :class="{ 'drag-over': isDragOver }">
                                                <template x-if="fields.length === 0">
                                                    <div class="empty-state text-center py-5">
                                                        <i class="fa fa-arrow-left fa-2x text-muted mb-3"></i>
                                                        <p class="text-muted">Drag elements from the right panel to build your form →</p>
                                                    </div>
                                                </template>
                                                <template x-if="fields.length > 0">
                                                    <div class="fields-list" x-ref="fieldsList">
                                                        <template x-for="(field, index) in fields" :key="field.id">
                                                            <div class="field-card"
                                                                 :class="{ 'editing': editingFieldId === field.id }"
                                                                 draggable="true"
                                                                 @dragstart="onDragStart(index)"
                                                                 @dragend="onDragEnd"
                                                                 @dragover.prevent
                                                                 @drop.prevent="onDropInCanvas(index)">
                                                                <div class="field-card-header">
                                                                    <div class="drag-handle" 
                                                                         draggable="true"
                                                                         @dragstart="onDragStart(index)">
                                                                        <i class="fa fa-grip-vertical"></i>
                                                                    </div>
                                                                    <span class="field-type-badge" x-text="field.type"></span>
                                                                    <div class="field-actions">
                                                                        <button class="btn btn-sm btn-icon" @click="editField(field.id)" title="Edit">
                                                                            <i class="fa fa-pencil"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-icon" @click="duplicateField(field.id)" title="Duplicate">
                                                                            <i class="fa fa-copy"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-icon text-danger" @click="confirmDelete(field.id)" title="Delete">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="field-card-body">
                                                                    <!-- Render field based on type -->
                                                                    <div x-html="renderFieldPreview(field)"></div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link" :class="{ 'active': activeRightTab === 'add' }" 
                                                       href="#" @click.prevent="activeRightTab = 'add'">
                                                        Add Fields
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" :class="{ 'active': activeRightTab === 'options' }" 
                                                       href="#" @click.prevent="activeRightTab = 'options'">
                                                        Field Options
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-body">
                                            <div x-show="activeRightTab === 'add'" x-cloak>
                                                <div class="field-types-grid">
                                                    <template x-for="fieldType in fieldTypes" :key="fieldType.type">
                                                        <div class="field-type-tile"
                                                             draggable="true"
                                                             @dragstart="onDragStartFieldType(fieldType)"
                                                             @dragend="onDragEnd">
                                                            <i :class="fieldType.icon"></i>
                                                            <span x-text="fieldType.label"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <div x-show="activeRightTab === 'options'" x-cloak>
                                                <template x-if="selectedField">
                                                    <div>
                                                        <form @submit.prevent="updateField">
                                                            <div class="form-group">
                                                                <label>Label</label>
                                                                <input type="text" class="form-control" x-model="selectedField.label">
                                                            </div>
                                                            
                                                            <div class="form-group" x-show="showPlaceholder(selectedField.type)">
                                                                <label>Placeholder</label>
                                                                <input type="text" class="form-control" x-model="selectedField.placeholder">
                                                            </div>
                                                            
                                                            <div class="form-group" x-show="showMinMax(selectedField.type)">
                                                                <label>Min Characters</label>
                                                                <input type="number" class="form-control" x-model="selectedField.minChars">
                                                            </div>
                                                            
                                                            <div class="form-group" x-show="showMinMax(selectedField.type)">
                                                                <label>Max Characters</label>
                                                                <input type="number" class="form-control" x-model="selectedField.maxChars">
                                                            </div>
                                                            
                                                            <div class="form-group" x-show="showOptionsList(selectedField.type)">
                                                                <label>Options</label>
                                                                <div class="options-list">
                                                                    <template x-for="(option, idx) in selectedField.options" :key="idx">
                                                                        <div class="input-group mb-2">
                                                                            <input type="text" class="form-control" x-model="selectedField.options[idx]">
                                                                            <div class="input-group-append">
                                                                                <button class="btn btn-danger" type="button" @click="removeOption(idx)">×</button>
                                                                            </div>
                                                                        </div>
                                                                    </template>
                                                                    <button type="button" class="btn btn-sm btn-secondary" @click="addOption">+ Add Option</button>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>CSS Class</label>
                                                                <input type="text" class="form-control" x-model="selectedField.cssClass">
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Default Value</label>
                                                                <input type="text" class="form-control" x-model="selectedField.defaultValue">
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox" class="custom-control-input" id="required-toggle" x-model="selectedField.required">
                                                                    <label class="custom-control-label" for="required-toggle">Required Field</label>
                                                                </div>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-primary btn-block">Apply Changes</button>
                                                            
                                                            <hr>
                                                            
                                                            <button type="button" class="btn btn-danger btn-block" @click="removeField(selectedField.id)">
                                                                Remove Element
                                                            </button>
                                                        </form>
                                                    </div>
                                                </template>
                                                <template x-if="!selectedField">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fa fa-pencil-square-o fa-2x mb-2"></i>
                                                        <p>Select a field to edit its options</p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn btn-outline-secondary" @click="cancelForm">Cancel</button>
                                <button class="btn btn-primary" @click="showSchema">Next →</button>
                            </div>
                        </div>

                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="text-center py-5">
                                <i class="fa fa-cogs fa-3x text-muted mb-3"></i>
                                <h5>Form Settings</h5>
                                <p class="text-muted">Additional settings will be available here</p>
                                <div class="form-group">
                                    <label>Enable Preview Mode</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="preview-mode" x-model="previewMode">
                                        <label class="custom-control-label" for="preview-mode">Live Preview</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }

.form-builder-container {
    min-height: 600px;
}

.drop-canvas {
    min-height: 400px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.drop-canvas.drag-over {
    border-color: #007bff;
    background: #e7f1ff;
    border-style: solid;
}

.empty-state {
    text-align: center;
    color: #6c757d;
}

.field-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 16px;
    transition: all 0.3s ease;
    cursor: move;
}

.field-card.editing {
    border: 2px solid #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.field-card-header {
    background: #f8f9fa;
    padding: 10px 15px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    gap: 12px;
}

.drag-handle {
    cursor: grab;
    color: #6c757d;
}

.drag-handle:active {
    cursor: grabbing;
}

.field-type-badge {
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #495057;
    font-weight: 500;
}

.field-actions {
    margin-left: auto;
    display: flex;
    gap: 4px;
}

.field-actions .btn-icon {
    padding: 4px 8px;
    font-size: 12px;
}

.field-card-body {
    padding: 15px;
}

.field-card-body input,
.field-card-body textarea,
.field-card-body select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.field-types-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.field-type-tile {
    padding: 12px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    text-align: center;
    cursor: grab;
    transition: all 0.2s ease;
    background: white;
}

.field-type-tile:hover {
    border-color: #007bff;
    background: #f8f9fa;
    transform: translateY(-2px);
}

.field-type-tile i {
    display: block;
    font-size: 24px;
    margin-bottom: 8px;
    color: #007bff;
}

.field-type-tile span {
    font-size: 12px;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
}

.options-list {
    max-height: 300px;
    overflow-y: auto;
}

@media (max-width: 1024px) {
    .field-types-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function formBuilder() {
    return {
        // State
        formTitle: 'Untitled Form',
        submitUrl: '',
        fields: [],
        fieldTypes: [
            { type: 'text', label: 'Text Input', icon: 'fa fa-font' },
            { type: 'textarea', label: 'Text Area', icon: 'fa fa-align-left' },
            { type: 'number', label: 'Number Input', icon: 'fa fa-hashtag' },
            { type: 'email', label: 'Email Input', icon: 'fa fa-envelope' },
            { type: 'tel', label: 'Phone Input', icon: 'fa fa-phone' },
            { type: 'select', label: 'Dropdown', icon: 'fa fa-caret-down' },
            { type: 'radio', label: 'Radio Buttons', icon: 'fa fa-dot-circle-o' },
            { type: 'checkbox', label: 'Checkboxes', icon: 'fa fa-check-square-o' },
            { type: 'date', label: 'Date Picker', icon: 'fa fa-calendar' },
            { type: 'file', label: 'File Upload', icon: 'fa fa-upload' },
            { type: 'heading', label: 'Title / Heading', icon: 'fa fa-header' },
            { type: 'paragraph', label: 'Description', icon: 'fa fa-paragraph' },
            { type: 'hidden', label: 'Hidden Field', icon: 'fa fa-eye-slash' }
        ],
        activeRightTab: 'add',
        selectedField: null,
        editingFieldId: null,
        isDragOver: false,
        dragSourceIndex: null,
        previewMode: false,
        
        // History for Undo/Redo
        history: [],
        historyIndex: -1,
        
        init() {
            this.loadFromStorage();
            this.saveToHistory();
            
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
                    e.preventDefault();
                    if (e.shiftKey) {
                        this.redo();
                    } else {
                        this.undo();
                    }
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
                    e.preventDefault();
                    this.redo();
                }
            });
        },
        
        updateTitle() {
            if (this.formTitle.length > 200) {
                this.formTitle = this.formTitle.substring(0, 200);
            }
            this.saveToStorage();
        },
        
        // Drag and Drop
        onDragStart(index) {
            this.dragSourceIndex = index;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', index);
        },
        
        onDragStartFieldType(fieldType) {
            event.dataTransfer.setData('field-type', JSON.stringify(fieldType));
            event.dataTransfer.effectAllowed = 'copy';
        },
        
        onDragEnd() {
            this.dragSourceIndex = null;
            this.isDragOver = false;
        },
        
        onDragOver() {
            this.isDragOver = true;
        },
        
        onDragLeave() {
            this.isDragOver = false;
        },
        
        onDrop() {
            this.isDragOver = false;
            const fieldTypeData = event.dataTransfer.getData('field-type');
            if (fieldTypeData) {
                const fieldType = JSON.parse(fieldTypeData);
                this.addField(fieldType);
            }
        },
        
        onDropInCanvas(targetIndex) {
            if (this.dragSourceIndex !== null && this.dragSourceIndex !== targetIndex) {
                this.reorderFields(this.dragSourceIndex, targetIndex);
            }
            this.dragSourceIndex = null;
        },
        
        addField(fieldType) {
            const newField = {
                id: Date.now() + Math.random(),
                type: fieldType.type,
                label: fieldType.label,
                placeholder: '',
                required: false,
                cssClass: '',
                defaultValue: '',
                options: ['Option 1', 'Option 2'],
                minChars: null,
                maxChars: null,
                value: ''
            };
            
            this.fields.push(newField);
            this.saveToStorage();
            this.saveToHistory();
        },
        
        reorderFields(fromIndex, toIndex) {
            const field = this.fields[fromIndex];
            this.fields.splice(fromIndex, 1);
            this.fields.splice(toIndex, 0, field);
            this.saveToStorage();
            this.saveToHistory();
        },
        
        editField(fieldId) {
            const field = this.fields.find(f => f.id === fieldId);
            if (field) {
                this.selectedField = JSON.parse(JSON.stringify(field));
                this.editingFieldId = fieldId;
                this.activeRightTab = 'options';
            }
        },
        
        updateField() {
            const index = this.fields.findIndex(f => f.id === this.selectedField.id);
            if (index !== -1) {
                this.fields[index] = JSON.parse(JSON.stringify(this.selectedField));
                this.selectedField = null;
                this.editingFieldId = null;
                this.saveToStorage();
                this.saveToHistory();
            }
        },
        
        duplicateField(fieldId) {
            const index = this.fields.findIndex(f => f.id === fieldId);
            if (index !== -1) {
                const duplicatedField = JSON.parse(JSON.stringify(this.fields[index]));
                duplicatedField.id = Date.now() + Math.random();
                this.fields.splice(index + 1, 0, duplicatedField);
                this.saveToStorage();
                this.saveToHistory();
            }
        },
        
        confirmDelete(fieldId) {
            if (confirm('Are you sure you want to remove this field?')) {
                this.removeField(fieldId);
            }
        },
        
        removeField(fieldId) {
            const index = this.fields.findIndex(f => f.id === fieldId);
            if (index !== -1) {
                this.fields.splice(index, 1);
                if (this.selectedField && this.selectedField.id === fieldId) {
                    this.selectedField = null;
                    this.editingFieldId = null;
                }
                this.saveToStorage();
                this.saveToHistory();
            }
        },
        
        addOption() {
            if (this.selectedField) {
                this.selectedField.options.push(`Option ${this.selectedField.options.length + 1}`);
            }
        },
        
        removeOption(index) {
            if (this.selectedField) {
                this.selectedField.options.splice(index, 1);
            }
        },
        
        showPlaceholder(type) {
            return ['text', 'textarea', 'email', 'tel', 'number'].includes(type);
        },
        
        showMinMax(type) {
            return ['text', 'textarea'].includes(type);
        },
        
        showOptionsList(type) {
            return ['select', 'radio', 'checkbox'].includes(type);
        },
        
        renderFieldPreview(field) {
            const required = field.required ? 'required' : '';
            const cssClass = field.cssClass || '';
            const value = field.defaultValue || '';
            
            switch(field.type) {
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                    return `<div class="form-group">
                                <label>${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                                <input type="${field.type}" class="form-control ${cssClass}" placeholder="${field.placeholder || ''}" value="${value}" ${required}>
                            </div>`;
                case 'textarea':
                    return `<div class="form-group">
                                <label>${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                                <textarea class="form-control ${cssClass}" placeholder="${field.placeholder || ''}" rows="3" ${required}>${value}</textarea>
                            </div>`;
                case 'select':
                    return `<div class="form-group">
                                <label>${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                                <select class="form-control ${cssClass}" ${required}>
                                    ${field.options.map(opt => `<option>${opt}</option>`).join('')}
                                </select>
                            </div>`;
                case 'radio':
                    return `<div class="form-group">
                                <label>${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                                ${field.options.map(opt => `
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" id="radio_${field.id}_${opt}" name="radio_${field.id}" value="${opt}">
                                        <label class="custom-control-label" for="radio_${field.id}_${opt}">${opt}</label>
                                    </div>
                                `).join('')}
                            </div>`;
                case 'checkbox':
                    return `<div class="form-group">
                                <label>${field.label}</label>
                                ${field.options.map(opt => `
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="checkbox_${field.id}_${opt}" value="${opt}">
                                        <label class="custom-control-label" for="checkbox_${field.id}_${opt}">${opt}</label>
                                    </div>
                                `).join('')}
                            </div>`;
                case 'date':
                    return `<div class="form-group">
                                <label>${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                                <input type="date" class="form-control ${cssClass}" value="${value}" ${required}>
                            </div>`;
                case 'file':
                    return `<div class="form-group">
                                <label>${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                                <input type="file" class="form-control-file ${cssClass}" ${required}>
                            </div>`;
                case 'heading':
                    return `<h3 class="${cssClass}">${field.label}</h3>`;
                case 'paragraph':
                    return `<p class="${cssClass}">${field.label}</p>`;
                case 'hidden':
                    return `<input type="hidden" name="${field.label}" value="${value}">`;
                default:
                    return `<div>${field.label}</div>`;
            }
        },
        
        getFormSchema() {
            return {
                formTitle: this.formTitle,
                submitUrl: this.submitUrl,
                fields: this.fields.map(field => ({
                    id: field.id,
                    type: field.type,
                    label: field.label,
                    placeholder: field.placeholder,
                    required: field.required,
                    cssClass: field.cssClass,
                    defaultValue: field.defaultValue,
                    options: field.options,
                    minChars: field.minChars,
                    maxChars: field.maxChars
                })),
                createdAt: new Date().toISOString(),
                version: '1.0'
            };
        },
        
        showSchema() {
            const schema = this.getFormSchema();
            console.log('Form Schema JSON:', JSON.stringify(schema, null, 2));
            alert('Form schema saved to console!\n\n' + JSON.stringify(schema, null, 2));
        },
        
        cancelForm() {
            if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                this.formTitle = 'Untitled Form';
                this.submitUrl = '';
                this.fields = [];
                this.saveToStorage();
                this.saveToHistory();
            }
        },
        
        // Undo/Redo
        saveToHistory() {
            const currentState = {
                formTitle: this.formTitle,
                submitUrl: this.submitUrl,
                fields: JSON.parse(JSON.stringify(this.fields))
            };
            
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(currentState);
            this.historyIndex++;
        },
        
        undo() {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                this.restoreFromHistory();
            }
        },
        
        redo() {
            if (this.historyIndex < this.history.length - 1) {
                this.historyIndex++;
                this.restoreFromHistory();
            }
        },
        
        restoreFromHistory() {
            const state = this.history[this.historyIndex];
            if (state) {
                this.formTitle = state.formTitle;
                this.submitUrl = state.submitUrl;
                this.fields = JSON.parse(JSON.stringify(state.fields));
                this.selectedField = null;
                this.editingFieldId = null;
            }
        },
        
        saveToStorage() {
            const data = {
                formTitle: this.formTitle,
                submitUrl: this.submitUrl,
                fields: this.fields,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('formBuilderData', JSON.stringify(data));
        },
        
        loadFromStorage() {
            const saved = localStorage.getItem('formBuilderData');
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    this.formTitle = data.formTitle || 'Untitled Form';
                    this.submitUrl = data.submitUrl || '';
                    this.fields = data.fields || [];
                } catch(e) {
                    console.error('Failed to load saved data', e);
                }
            }
        }
    };
}
</script>
@endsection