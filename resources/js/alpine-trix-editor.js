// resources/js/alpine-trix-editor.js

document.addEventListener('alpine:init', () => {
    Alpine.data('trixEditor', () => ({
        editorId: null,
        hiddenInputId: null,
        editor: null,
        
        init() {
            this.editorId = this.$el.dataset.editorId || 'trix-' + Date.now();
            this.hiddenInputId = this.$el.dataset.hiddenId || this.editorId + '-hidden';
            
            this.$nextTick(() => {
                this.setupEditor();
                this.applyDarkMode();
                this.setupDarkModeObserver();
            });
        },
        
        setupEditor() {
            const editorElement = document.getElementById(this.editorId);
            const hiddenInput = document.getElementById(this.hiddenInputId);
            
            if (!editorElement || !hiddenInput) return;
            
            this.editor = editorElement;
            
            // Sync content changes to Livewire
            editorElement.addEventListener('trix-change', () => {
                hiddenInput.value = editorElement.innerHTML;
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Trigger Livewire update
                if (window.Livewire) {
                    const component = window.Livewire.find(
                        editorElement.closest('[wire\\:id]')?.getAttribute('wire:id')
                    );
                    if (component) {
                        component.set(hiddenInput.name || 'content', hiddenInput.value);
                    }
                }
            });
            
            // Initialize with existing content
            editorElement.addEventListener('trix-initialize', () => {
                if (hiddenInput.value) {
                    editorElement.editor.loadHTML(hiddenInput.value);
                }
                this.applyDarkMode();
            });
            
            // Handle focus events
            editorElement.addEventListener('trix-focus', () => {
                this.applyDarkMode();
            });
            
            // Listen for trix-update events from Livewire
            window.addEventListener('trix-update', e => {
                const { target, content } = e.detail;
                // Check if this is the right target (by name attribute)
                if (target !== hiddenInput.name) return;
                
                // Update the hidden input value
                hiddenInput.value = content || '';
                
                // Update Trix editor content
                if (editorElement.editor) {
                    editorElement.editor.loadHTML(content || '');
                } else {
                    // If editor not ready, wait for initialization
                    editorElement.addEventListener('trix-initialize', () => {
                        editorElement.editor.loadHTML(content || '');
                    }, { once: true });
                }
            });
        },
        
        applyDarkMode() {
            if (!this.editor) return;
            
            const isDark = document.documentElement.classList.contains('dark');
            const toolbar = document.querySelector(`trix-toolbar[id="${this.editorId}"]`);
            
            // Apply dark mode classes
            if (isDark) {
                this.editor.classList.add('dark-mode');
                if (toolbar) {
                    toolbar.classList.add('dark-mode');
                }
            } else {
                this.editor.classList.remove('dark-mode');
                if (toolbar) {
                    toolbar.classList.remove('dark-mode');
                }
            }
        },
        
        setupDarkModeObserver() {
            // Watch for dark mode changes
            const observer = new MutationObserver(() => {
                this.applyDarkMode();
            });
            
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        },
        
        // Method to set content programmatically
        setContent(content) {
            if (this.editor && this.editor.editor) {
                this.editor.editor.loadHTML(content || '');
            }
        },
        
        // Method to get content
        getContent() {
            return this.editor ? this.editor.innerHTML : '';
        },
        
        // Method to clear content
        clearContent() {
            if (this.editor && this.editor.editor) {
                this.editor.editor.loadHTML('');
            }
        }
    }));
});

// Global helper functions
window.TrixEditor = {
    setContent(editorId, content) {
        const element = document.querySelector(`[data-editor-id="${editorId}"]`);
        if (element && element.__x) {
            element.__x.$data.setContent(content);
        }
    },
    
    getContent(editorId) {
        const element = document.querySelector(`[data-editor-id="${editorId}"]`);
        if (element && element.__x) {
            return element.__x.$data.getContent();
        }
        return '';
    },
    
    clearContent(editorId) {
        const element = document.querySelector(`[data-editor-id="${editorId}"]`);
        if (element && element.__x) {
            element.__x.$data.clearContent();
        }
    }
};

// Event listeners for Livewire integration
document.addEventListener('livewire:updated', function() {
    // Re-apply dark mode after Livewire updates
    document.querySelectorAll('trix-editor').forEach(editor => {
        const isDark = document.documentElement.classList.contains('dark');
        const toolbar = document.querySelector(`trix-toolbar[id="${editor.id}"]`);
        
        if (isDark) {
            editor.classList.add('dark-mode');
            if (toolbar) {
                toolbar.classList.add('dark-mode');
            }
        } else {
            editor.classList.remove('dark-mode');
            if (toolbar) {
                toolbar.classList.remove('dark-mode');
            }
        }
    });
});