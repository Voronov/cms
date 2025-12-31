document.addEventListener('alpine:init', () => {
    Alpine.data('formBuilder', (config) => ({
        title: config.title || 'New Form',
        description: config.description || '',
        identifier: config.identifier || '',
        isActive: config.isActive !== undefined ? config.isActive : true,
        fields: config.fields || [],

        fieldTypes: [
            { id: 'text', label: 'Short Text', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>' },
            { id: 'textarea', label: 'Long Text', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>' },
            { id: 'email', label: 'Email', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' },
            { id: 'number', label: 'Number', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>' },
            { id: 'select', label: 'Select Dropdown', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>' },
            { id: 'checkbox', label: 'Single Checkbox', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' },
            { id: 'file', label: 'File Upload', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>' },
        ],

        init() {
            // Initialize Palette
            new Sortable(document.getElementById('palette'), {
                group: {
                    name: 'shared',
                    pull: 'clone',
                    put: false
                },
                sort: false,
                animation: 150,
                onEnd: (evt) => {
                    // Prevent Sortable from actually moving the element
                    // as Alpine needs to manage the DOM
                    if (evt.to.id === 'canvas') {
                        evt.item.remove();
                    }
                }
            });

            // Initialize Canvas
            new Sortable(document.getElementById('canvas'), {
                group: 'shared',
                animation: 150,
                handle: '.cursor-move',
                onAdd: (evt) => {
                    const typeId = evt.item.getAttribute('data-type');
                    const index = evt.newIndex;
                    // Remove the cloned element immediately so Alpine can render its own
                    evt.item.remove();
                    this.addField(typeId, index);
                },
                onUpdate: (evt) => {
                    // Reorder the fields array when sorting within canvas
                    const item = this.fields.splice(evt.oldIndex, 1)[0];
                    this.fields.splice(evt.newIndex, 0, item);
                }
            });

            // Prepare options_text for existing fields
            this.fields.forEach(f => {
                if (f.options) f.options_text = f.options.join('\n');
            });
        },

        addField(typeId, index) {
            const type = this.fieldTypes.find(t => t.id === typeId);
            const newField = {
                id: 'f' + Date.now(),
                type: typeId,
                label: type.label,
                name: '',
                required: false,
                options: [],
                options_text: '',
                extensions: '',
                max_size: '',
                validation_message: '',
                validation_rule: 'none',
                validation_regex: '',
                mask: ''
            };

            this.fields.splice(index, 0, newField);
        },

        removeField(index) {
            if (confirm('Remove this field?')) {
                this.fields.splice(index, 1);
            }
        },

        applyRuleDefaults(index) {
            const field = this.fields[index];
            if (field.validation_rule === 'phone') {
                field.mask = '(999) 999-9999';
                field.validation_regex = '^\\(\\d{3}\\) \\d{3}-\\d{4}$';
            } else if (field.validation_rule === 'email') {
                field.validation_regex = ''; 
            } else if (field.validation_rule === 'number') {
                field.validation_regex = '^[0-9]+$';
            } else if (field.validation_rule === 'alpha') {
                field.validation_regex = '^[a-zA-Z0-9]+$';
            } else if (field.validation_rule === 'none') {
                field.validation_regex = '';
            }
        },

        updateOptions(index) {
            const field = this.fields[index];
            field.options = field.options_text.split('\n').map(s => s.trim()).filter(s => s !== '');
        },

        slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '_')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '_')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        },

        async saveForm() {
            const payload = {
                title: this.title,
                description: this.description,
                is_active: this.isActive,
                fields: this.fields.map(f => {
                    if (!f.name) f.name = this.slugify(f.label);
                    return f;
                })
            };

            const url = this.identifier
                ? config.updateUrl.replace(':id', this.identifier)
                : config.storeUrl;

            const method = this.identifier ? 'PATCH' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    window.location.href = config.indexUrl;
                } else {
                    const data = await response.json();
                    alert('Error: ' + (data.message || 'Validation failed'));
                }
            } catch (error) {
                console.error('Save failed', error);
            }
        }
    }));
});
