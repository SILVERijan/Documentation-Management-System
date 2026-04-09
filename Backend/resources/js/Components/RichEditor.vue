<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import { 
  Bold, Italic, Underline as UnderlineIcon, List, ListOrdered, 
  Quote, Code, Heading1, Heading2, Link as LinkIcon, Unlink,
  Undo, Redo, Image as ImageIcon, Loader2
} from 'lucide-vue-next'
import { watch, onBeforeUnmount, ref } from 'vue'
import axios from 'axios'

const props = defineProps<{
  modelValue: string;
  placeholder?: string;
}>()

const emit = defineEmits(['update:modelValue'])
const isUploading = ref(false)

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Underline,
    Link.configure({
      openOnClick: false,
    }),
    Image.configure({
      allowBase64: true,
      HTMLAttributes: {
        class: 'rounded-xl border border-[#262626] max-w-full my-4 shadow-lg mx-auto block',
      },
    }),
  ],
  editorProps: {
    handlePaste(view, event) {
        const items = Array.from(event.clipboardData?.items || [])
        const imageItem = items.find(item => item.type.startsWith('image'))

        if (imageItem) {
            const file = imageItem.getAsFile()
            if (file) {
                uploadImage(file)
                return true // Handled
            }
        }
        return false // Default behavior for other items
    },
    handleDrop(view, event, slice, moved) {
        if (!moved && event.dataTransfer?.files.length) {
            const file = event.dataTransfer.files[0]
            if (file.type.startsWith('image')) {
                uploadImage(file)
                return true
            }
        }
        return false
    },
    attributes: {
      class: 'prose prose-invert max-w-none focus:outline-none min-h-[350px] p-8 text-sm text-gray-300',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

// Sync internal editor content with prop changes
watch(() => props.modelValue, (value) => {
  if (editor.value && editor.value.getHTML() !== value) {
    editor.value.commands.setContent(value, { emitUpdate: false } as any)
  }
})

onBeforeUnmount(() => {
  editor.value?.destroy()
})

const uploadImage = async (file: File) => {
    isUploading.value = true
    const formData = new FormData()
    formData.append('image', file)

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        const response = await axios.post(route('media.upload'), formData, {
            headers: {
                'X-CSRF-TOKEN': token || '',
                'Content-Type': 'multipart/form-data'
            }
        })
        
        if (response.data && response.data.url) {
            editor.value?.chain().focus().setImage({ src: response.data.url }).run()
        } else {
            throw new Error('Invalid response format')
        }
    } catch (error: any) {
        console.error('Image upload failed:', error)
        const message = error.response?.data?.message || error.message || 'Check your internet connection.'
        alert(`Failed to upload image: ${message}`)
    } finally {
        isUploading.value = false
    }
}

const addImage = () => {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/*'
    input.onchange = (e: any) => {
        if (e.target.files.length) {
            uploadImage(e.target.files[0])
        }
    }
    input.click()
}

const setLink = () => {
  const previousUrl = editor.value?.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)

  if (url === null) return
  if (url === '') {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}
</script>

<template>
  <div v-if="editor" class="border border-[#262626] rounded-2xl overflow-hidden bg-[#1a1a1a] focus-within:border-indigo-500/50 transition-all shadow-inner">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-1 p-2 border-b border-[#262626] bg-[#161616]/50 backdrop-blur-sm sticky top-0 z-10">
      <button 
        type="button"
        @click="editor.chain().focus().toggleBold().run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('bold') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Bold"
      >
        <Bold class="w-4 h-4" />
      </button>
      <button 
        type="button"
        @click="editor.chain().focus().toggleItalic().run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('italic') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Italic"
      >
        <Italic class="w-4 h-4" />
      </button>
      <button 
        type="button"
        @click="editor.chain().focus().toggleUnderline().run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('underline') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Underline"
      >
        <UnderlineIcon class="w-4 h-4" />
      </button>
      
      <div class="w-px h-4 bg-[#262626] mx-1" />

      <button 
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('heading', { level: 1 }) ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Heading 1"
      >
        <Heading1 class="w-4 h-4" />
      </button>
      <button 
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('heading', { level: 2 }) ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Heading 2"
      >
        <Heading2 class="w-4 h-4" />
      </button>

      <div class="w-px h-4 bg-[#262626] mx-1" />

      <button 
        type="button"
        @click="editor.chain().focus().toggleBulletList().run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('bulletList') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Bullet List"
      >
        <List class="w-4 h-4" />
      </button>
      <button 
        type="button"
        @click="editor.chain().focus().toggleOrderedList().run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('orderedList') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Ordered List"
      >
        <ListOrdered class="w-4 h-4" />
      </button>

      <div class="w-px h-4 bg-[#262626] mx-1" />

      <button 
        type="button"
        @click="editor.chain().focus().toggleBlockquote().run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('blockquote') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Quote"
      >
        <Quote class="w-4 h-4" />
      </button>
      <button 
        type="button"
        @click="editor.chain().focus().toggleCodeBlock().run()"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('codeBlock') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Code Block"
      >
        <Code class="w-4 h-4" />
      </button>

      <div class="w-px h-4 bg-[#262626] mx-1" />

      <button 
        type="button"
        @click="addImage"
        :disabled="isUploading"
        class="p-2 rounded-lg hover:bg-white/5 transition-all text-gray-400 disabled:opacity-30"
        title="Insert Image"
      >
        <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin text-indigo-400" />
        <ImageIcon v-else class="w-4 h-4" />
      </button>

      <button 
        type="button"
        @click="setLink"
        :class="['p-2 rounded-lg hover:bg-white/5 transition-all', editor.isActive('link') ? 'text-indigo-400 bg-indigo-500/10' : 'text-gray-400']"
        title="Add Link"
      >
        <LinkIcon class="w-4 h-4" />
      </button>
      <button 
        type="button"
        @click="editor.chain().focus().unsetLink().run()"
        :disabled="!editor.isActive('link')"
        class="p-2 rounded-lg hover:bg-white/5 transition-all text-gray-400 disabled:opacity-30"
        title="Remove Link"
      >
        <Unlink class="w-4 h-4" />
      </button>

      <div class="flex-1" />

      <div class="flex items-center gap-0.5">
          <button 
            type="button"
            @click="editor.chain().focus().undo().run()"
            :disabled="!editor.can().undo()"
            class="p-2 rounded-lg hover:bg-white/5 transition-all text-gray-400 disabled:opacity-30"
          >
            <Undo class="w-4 h-4" />
          </button>
          <button 
            type="button"
            @click="editor.chain().focus().redo().run()"
            :disabled="!editor.can().redo()"
            class="p-2 rounded-lg hover:bg-white/5 transition-all text-gray-400 disabled:opacity-30"
          >
            <Redo class="w-4 h-4" />
          </button>
      </div>
    </div>

    <!-- Editor Content -->
    <EditorContent :editor="editor" />
  </div>
</template>

<style>
/* Tiptap specific resets and styles */
.ProseMirror {
    outline: none !important;
}

.prose pre {
  background-color: #0d0d0d !important;
  color: #fff !important;
  padding: 1rem !important;
  border-radius: 0.75rem !important;
  border: 1px solid #262626;
  font-family: 'JetBrains Mono', monospace;
}

.prose code {
  color: #818cf8 !important;
  background-color: rgba(129, 140, 248, 0.1) !important;
  padding: 0.2rem 0.4rem !important;
  border-radius: 0.375rem !important;
  font-weight: 500 !important;
}

.prose code::before, 
.prose code::after {
  content: "" !important;
}

.prose blockquote {
  border-left-width: 4px !important;
  border-left-color: #4f46e5 !important;
  padding-left: 1.25rem !important;
  font-style: italic !important;
  color: #d1d5db !important;
  background: rgba(79, 70, 229, 0.05);
  border-radius: 0 0.5rem 0.5rem 0;
  margin-left: 0;
  margin-right: 0;
  border-top: none;
  border-bottom: none;
  border-right: none;
}

/* Placeholder styling */
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #4b5563;
  pointer-events: none;
  height: 0;
}

/* Link styling in editor */
.ProseMirror a {
    color: #818cf8;
    text-decoration: underline;
    cursor: pointer;
}

/* Image styling */
.ProseMirror img {
    @apply rounded-2xl border border-[#262626] mx-auto block shadow-2xl transition-all hover:scale-[1.01];
}
</style>
