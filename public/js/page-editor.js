document.addEventListener('alpine:init', () => {
    Alpine.data('pageEditor', (config) => ({
        showSettings: config.showSettings !== undefined ? config.showSettings : false,
        mediaConfig: config.mediaConfig,
        pageBlocks: config.pageBlocks,
        blockTypes: config.blockTypes,
        showBlockPicker: false,
        entityTypes: {},
        cropping: {
            show: false,
            blockIndex: null,
            fieldName: null,
            imageUrl: null,
            path: null,
            instance: null
        },
        showSlugModal: false,
        pendingSlug: '',

        async init() {
            try {
                const response = await fetch('/admin/api/entities/types');
                this.entityTypes = await response.json();
                
                this.pageBlocks.forEach((block, index) => {
                    if (block.data && block.data.entity_type) {
                        this.loadCategories(index, block.data.entity_type);
                    }
                });

                this.initSortable();
            } catch (error) {
                console.error('Failed to load entity types:', error);
            }
        },

        initSortable() {
            this.$nextTick(() => {
                const el = document.getElementById('blocks-list');
                if (!el) return;

                new Sortable(el, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'bg-indigo-50',
                    onEnd: (evt) => {
                        const oldIndex = evt.oldIndex;
                        const newIndex = evt.newIndex;
                        
                        if (oldIndex !== newIndex) {
                            const movedBlock = this.pageBlocks.splice(oldIndex, 1)[0];
                            this.pageBlocks.splice(newIndex, 0, movedBlock);
                        }
                    }
                });
            });
        },

        async loadCategories(blockIndex, entityType) {
            if (!entityType) return;
            try {
                const response = await fetch(`/admin/api/entities/${entityType}/categories`);
                const categories = await response.json();
                this.pageBlocks[blockIndex].categories = categories;
            } catch (error) {
                console.error('Failed to load categories:', error);
            }
        },

        async loadEntityItems(blockIndex, entityType) {
            if (!entityType) return;
            try {
                const response = await fetch(`/admin/api/entities/${entityType}/items`);
                const items = await response.json();
                this.pageBlocks[blockIndex].entityItems = items;
            } catch (error) {
                console.error('Failed to load entity items:', error);
            }
        },

        async loadPaginationOptions(blockIndex, entityType) {
            if (!entityType) return;
            try {
                const response = await fetch(`/admin/api/entities/${entityType}/pagination`);
                const options = await response.json();
                
                if (!this.pageBlocks[blockIndex].paginationOptions) {
                    this.pageBlocks[blockIndex].paginationOptions = {};
                }
                this.pageBlocks[blockIndex].paginationOptions = options;
            } catch (error) {
                console.error('Failed to load pagination options:', error);
            }
        },

        async saveBlock(blockIndex) {
            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch(`/admin/pages/${config.pageId}/blocks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        locale: config.locale,
                        blocks: this.pageBlocks
                    })
                });
                
                if (response.ok) {
                    // Collapse the block on successful save
                    if (blockIndex !== undefined && this.pageBlocks[blockIndex]) {
                        this.pageBlocks[blockIndex].collapsed = true;
                    }

                    const successDiv = document.createElement('div');
                    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successDiv.textContent = 'Block saved successfully!';
                    document.body.appendChild(successDiv);
                    setTimeout(() => successDiv.remove(), 3000);
                } else {
                    throw new Error('Failed to save block');
                }
            } catch (error) {
                console.error('Failed to save block:', error);
                alert('Failed to save block. Please try again.');
            }
        },

        confirmSlugRegeneration() {
            let title = document.getElementById('title').value;
            let slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();

            if (slug) {
                this.pendingSlug = slug;
                this.showSlugModal = true;
            } else {
                alert('Please enter a title first.');
            }
        },

        applySlug() {
            document.getElementById('slug').value = this.pendingSlug;
            this.showSlugModal = false;
        },

        addBlock(typeId) {
            const blockType = this.blockTypes.find(b => b.id === typeId);
            const newBlock = {
                id: 'block-' + Date.now(),
                type: typeId,
                name: blockType.name,
                data: {},
                uploads: {},
                isEditing: true,
                collapsed: false
            };
            blockType.fields.forEach(field => {
                newBlock.data[field.name] = field.type === 'select' ? (field.default || Object.keys(field.options || {})[0] || '') : '';
            });
            this.pageBlocks.push(newBlock);
            this.showBlockPicker = false;
        },

        toggleBlockCollapse(index) {
            if (this.pageBlocks[index].collapsed === undefined) {
                this.pageBlocks[index].collapsed = false;
            }
            this.pageBlocks[index].collapsed = !this.pageBlocks[index].collapsed;
        },

        toggleBlockEdit(index) {
            this.pageBlocks[index].isEditing = !this.pageBlocks[index].isEditing;
        },

        renderPreview(block) {
            const blockType = this.blockTypes.find(b => b.id === block.type);
            if (!blockType || !blockType.preview || !blockType.preview.template) {
                return '<div class="p-4 text-gray-500 italic">No preview available</div>';
            }
            
            let template = blockType.preview.template;
            const openBrace = '[[';
            const closeBrace = ']]';
            
            // Replace placeholders with actual data or formatted values
            Object.keys(block.data).forEach(key => {
                const field = blockType.fields.find(f => f.name === key);
                let value = block.data[key];

                // For select fields, try to get the label instead of the key
                if (field && field.type === 'select' && field.options && field.options[value]) {
                    value = field.options[value];
                }

                const pattern1 = openBrace + ' ' + key + ' ' + closeBrace;
                const pattern2 = openBrace + key + closeBrace;
                
                template = template.split(pattern1).join(value || '');
                template = template.split(pattern2).join(value || '');
            });
            
            // Handle defaults and clean up remaining placeholders
            let startIndex = 0;
            while (true) {
                const start = template.indexOf(openBrace, startIndex);
                if (start === -1) break;
                
                const end = template.indexOf(closeBrace, start);
                if (end === -1) break;
                
                const fullMatch = template.substring(start, end + 2);
                if (fullMatch.includes('||')) {
                    const parts = fullMatch.split('||');
                    const defaultValue = parts[1].trim().replace(/['"]/g, '').replace(/\]\]/g, '').trim();
                    template = template.substring(0, start) + defaultValue + template.substring(end + 2);
                } else {
                    template = template.substring(0, start) + template.substring(end + 2);
                }
            }
            
            return template;
        },

        removeBlock(index) {
            if(confirm('Remove this block?')) {
                this.pageBlocks.splice(index, 1);
            }
        },

        moveBlock(index, direction) {
            const newIndex = index + direction;
            if (newIndex >= 0 && newIndex < this.pageBlocks.length) {
                const movedBlock = this.pageBlocks.splice(index, 1)[0];
                this.pageBlocks.splice(newIndex, 0, movedBlock);
            }
        },

        async handleFileUpload(event, blockIndex, fieldName) {
            const file = event.target.files[0];
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();
            if (!this.mediaConfig.allowed_extensions.includes(ext)) {
                alert('Invalid file type.');
                return;
            }
            if (file.size > this.mediaConfig.max_file_size) {
                alert('File too large.');
                return;
            }

            const identifier = Math.random().toString(36).substring(7);
            const chunkSize = this.mediaConfig.chunk_size;
            const totalChunks = Math.ceil(file.size / chunkSize);

            this.pageBlocks[blockIndex].uploads = this.pageBlocks[blockIndex].uploads || {};
            this.pageBlocks[blockIndex].uploads[fieldName] = { progress: 0, status: 'uploading' };

            for (let i = 0; i < totalChunks; i++) {
                const start = i * chunkSize;
                const end = Math.min(file.size, start + chunkSize);
                const chunk = file.slice(start, end);

                const formData = new FormData();
                formData.append('file', chunk);
                formData.append('identifier', identifier);
                formData.append('chunk_index', i);
                formData.append('total_chunks', totalChunks);
                formData.append('filename', file.name);
                formData.append('_token', document.querySelector('meta[name=csrf-token]').content);

                try {
                    const response = await fetch('/admin/media/upload', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    this.pageBlocks[blockIndex].uploads[fieldName].progress = Math.round(((i + 1) / totalChunks) * 100);

                    if (result.url) {
                        this.pageBlocks[blockIndex].data[fieldName] = result.url;
                        this.pageBlocks[blockIndex].data[fieldName + '_path'] = result.path;
                        this.pageBlocks[blockIndex].uploads[fieldName].status = 'complete';
                        
                        if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                            this.initCropper(blockIndex, fieldName, result.url, result.path);
                        }
                    }
                } catch (error) {
                    console.error('Upload failed', error);
                    this.pageBlocks[blockIndex].uploads[fieldName].status = 'error';
                    break;
                }
            }
        },

        initCropper(blockIndex, fieldName, imageUrl, path) {
            this.cropping.blockIndex = blockIndex;
            this.cropping.fieldName = fieldName;
            this.cropping.imageUrl = imageUrl;
            this.cropping.path = path;
            this.cropping.show = true;

            setTimeout(() => {
                const image = document.getElementById('cropper-image');
                if (this.cropping.instance) this.cropping.instance.destroy();
                this.cropping.instance = new Cropper(image, {
                    aspectRatio: NaN,
                    viewMode: 1,
                });
            }, 100);
        },

        async applyCrop() {
            const data = this.cropping.instance.getData();
            const response = await fetch('/admin/media/crop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    path: this.cropping.path,
                    x: Math.round(data.x),
                    y: Math.round(data.y),
                    width: Math.round(data.width),
                    height: Math.round(data.height)
                })
            });

            const result = await response.json();
            if (result.success) {
                this.pageBlocks[this.cropping.blockIndex].data[this.cropping.fieldName] = result.url;
                this.pageBlocks[this.cropping.blockIndex].data[this.cropping.fieldName + '_path'] = result.path;
                this.closeCropper();
            }
        },

        closeCropper() {
            if (this.cropping.instance) this.cropping.instance.destroy();
            this.cropping.show = false;
        }
    }));
});
