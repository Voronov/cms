document.addEventListener('alpine:init', () => {
    Alpine.data('repeaterField', (config) => ({
        items: config.existingData || [],
        fieldName: config.fieldName,
        fields: config.fields,
        
        init() {
            if (this.items.length === 0) {
                this.addItem();
            } else {
                this.render();
            }
        },
        
        addItem() {
            const newItem = {};
            this.fields.forEach(field => {
                newItem[field.name] = field.default || '';
            });
            this.items.push(newItem);
            this.renderItem(this.items.length - 1);
        },
        
        removeItem(index) {
            if (confirm('Remove this item?')) {
                this.items.splice(index, 1);
                this.render();
            }
        },
        
        render() {
            this.$refs.container.innerHTML = '';
            this.items.forEach((item, index) => {
                this.renderItem(index);
            });
        },
        
        renderItem(index) {
            const item = this.items[index];
            const itemDiv = document.createElement('div');
            itemDiv.className = 'mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700';
            
            let fieldsHtml = '';
            this.fields.forEach(field => {
                const inputName = `${this.fieldName}[${index}][${field.name}]`;
                const value = item[field.name] || '';
                
                fieldsHtml += `
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            ${field.label}${field.required ? ' <span class="text-red-500">*</span>' : ''}
                        </label>`;
                
                if (field.type === 'file') {
                    fieldsHtml += `
                        <input type="file" 
                               name="${inputName}" 
                               accept="${field.accept || '*'}"
                               ${field.required ? 'required' : ''}
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm">
                        ${field.max_size ? `<p class="mt-1 text-xs text-gray-500">Max size: ${(field.max_size / 1024).toFixed(1)}MB</p>` : ''}`;
                } else if (field.type === 'textarea') {
                    fieldsHtml += `
                        <textarea name="${inputName}" 
                                  rows="3"
                                  placeholder="${field.placeholder || ''}"
                                  ${field.required ? 'required' : ''}
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm">${value}</textarea>`;
                } else if (field.type === 'number') {
                    fieldsHtml += `
                        <input type="number" 
                               name="${inputName}" 
                               value="${value}"
                               placeholder="${field.placeholder || ''}"
                               ${field.required ? 'required' : ''}
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm">`;
                } else {
                    fieldsHtml += `
                        <input type="text" 
                               name="${inputName}" 
                               value="${value}"
                               placeholder="${field.placeholder || ''}"
                               ${field.required ? 'required' : ''}
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm">`;
                }
                
                fieldsHtml += `</div>`;
            });
            
            itemDiv.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Item ${index + 1}</h4>
                    <button type="button" 
                            onclick="window.Alpine.$data(this).removeItem(${index})"
                            class="text-red-600 hover:text-red-800 text-sm">
                        Remove
                    </button>
                </div>
                ${fieldsHtml}
            `;
            
            this.$refs.container.appendChild(itemDiv);
        }
    }));
});
