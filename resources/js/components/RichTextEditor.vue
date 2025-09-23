<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useVModel } from '@vueuse/core';
import { Bold, Italic, List, ListOrdered, Link, Underline, AlignLeft, AlignCenter, AlignRight } from 'lucide-vue-next';
import { ref, onMounted, nextTick } from 'vue';

interface Props {
  modelValue?: string;
  placeholder?: string;
  height?: number;
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Tulis konten di sini...',
  height: 400,
  disabled: false,
});

const emits = defineEmits<{
  'update:modelValue': [value: string];
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
});

const editorRef = ref<HTMLDivElement>();

const executeCommand = (command: string, value: string | undefined = undefined) => {
  document.execCommand(command, false, value);
  editorRef.value?.focus();
  updateContent();
};

const updateContent = () => {
  if (editorRef.value) {
    modelValue.value = editorRef.value.innerHTML;
  }
};

const insertLink = () => {
  const url = prompt('Masukkan URL:');
  if (url) {
    executeCommand('createLink', url);
  }
};

const onInput = () => {
  updateContent();
};

const onPaste = (event: ClipboardEvent) => {
  event.preventDefault();
  const text = event.clipboardData?.getData('text/plain') || '';
  executeCommand('insertText', text);
};

onMounted(() => {
  if (editorRef.value && modelValue.value) {
    editorRef.value.innerHTML = modelValue.value;
  }
});
</script>

<template>
  <div class="simple-rich-editor border rounded-lg overflow-hidden">
    <!-- Toolbar -->
    <div class="toolbar border-b bg-gray-50 dark:bg-gray-800 p-2 flex flex-wrap gap-1">
      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('bold')"
        title="Bold"
        type="button"
      >
        <Bold class="h-4 w-4" />
      </Button>

      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('italic')"
        title="Italic"
        type="button"
      >
        <Italic class="h-4 w-4" />
      </Button>

      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('underline')"
        title="Underline"
        type="button"
      >
        <Underline class="h-4 w-4" />
      </Button>

      <div class="border-l mx-1"></div>

      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('justifyLeft')"
        title="Align Left"
        type="button"
      >
        <AlignLeft class="h-4 w-4" />
      </Button>

      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('justifyCenter')"
        title="Align Center"
        type="button"
      >
        <AlignCenter class="h-4 w-4" />
      </Button>

      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('justifyRight')"
        title="Align Right"
        type="button"
      >
        <AlignRight class="h-4 w-4" />
      </Button>

      <div class="border-l mx-1"></div>

      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('insertUnorderedList')"
        title="Bullet List"
        type="button"
      >
        <List class="h-4 w-4" />
      </Button>

      <Button
        variant="ghost"
        size="sm"
        @click="executeCommand('insertOrderedList')"
        title="Numbered List"
        type="button"
      >
        <ListOrdered class="h-4 w-4" />
      </Button>

      <Button
        variant="ghost"
        size="sm"
        @click="insertLink"
        title="Insert Link"
        type="button"
      >
        <Link class="h-4 w-4" />
      </Button>

      <div class="border-l mx-1"></div>

      <select
        @change="(e) => executeCommand('formatBlock', (e.target as HTMLSelectElement).value)"
        class="text-sm border rounded px-2 py-1"
      >
        <option value="">Format</option>
        <option value="h1">Heading 1</option>
        <option value="h2">Heading 2</option>
        <option value="h3">Heading 3</option>
        <option value="p">Paragraph</option>
      </select>
    </div>

    <!-- Editor Content -->
    <div
      ref="editorRef"
      class="editor-content p-4 outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:border-ring"
      :style="{ minHeight: `${height}px` }"
      contenteditable="true"
      :data-placeholder="placeholder"
      @input="onInput"
      @paste="onPaste"
      :aria-disabled="disabled"
    ></div>
  </div>
</template>

<style scoped>
.editor-content:empty::before {
  content: attr(data-placeholder);
  color: rgb(156 163 175);
  pointer-events: none;
}

.editor-content[aria-disabled="true"] {
  background-color: rgb(243 244 246);
  cursor: not-allowed;
  opacity: 0.6;
}

/* Styling for content inside editor */
.editor-content :deep(h1) {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.editor-content :deep(h2) {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}

.editor-content :deep(h3) {
  font-size: 1.125rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.editor-content :deep(p) {
  margin-bottom: 0.5rem;
}

.editor-content :deep(ul) {
  list-style-type: disc;
  margin-left: 1.5rem;
  margin-bottom: 0.5rem;
}

.editor-content :deep(ol) {
  list-style-type: decimal;
  margin-left: 1.5rem;
  margin-bottom: 0.5rem;
}

.editor-content :deep(li) {
  margin-bottom: 0.25rem;
}

.editor-content :deep(a) {
  color: rgb(37 99 235);
  text-decoration: underline;
}

.editor-content :deep(a:hover) {
  color: rgb(29 78 216);
}

.editor-content :deep(strong) {
  font-weight: 700;
}

.editor-content :deep(em) {
  font-style: italic;
}

.editor-content :deep(u) {
  text-decoration: underline;
}
</style>