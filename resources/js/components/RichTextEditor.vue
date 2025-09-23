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
      class="editor-content p-4 outline-none"
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
.editor-content {
  @apply focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50;
}

.editor-content:empty::before {
  content: attr(data-placeholder);
  @apply text-gray-400 pointer-events-none;
}

.editor-content[aria-disabled="true"] {
  @apply bg-gray-100 cursor-not-allowed opacity-60;
}

/* Styling for content inside editor */
.editor-content :deep(h1) {
  @apply text-2xl font-bold mb-4;
}

.editor-content :deep(h2) {
  @apply text-xl font-bold mb-3;
}

.editor-content :deep(h3) {
  @apply text-lg font-bold mb-2;
}

.editor-content :deep(p) {
  @apply mb-2;
}

.editor-content :deep(ul) {
  @apply list-disc ml-6 mb-2;
}

.editor-content :deep(ol) {
  @apply list-decimal ml-6 mb-2;
}

.editor-content :deep(li) {
  @apply mb-1;
}

.editor-content :deep(a) {
  @apply text-blue-600 underline hover:text-blue-800;
}

.editor-content :deep(strong) {
  @apply font-bold;
}

.editor-content :deep(em) {
  @apply italic;
}

.editor-content :deep(u) {
  @apply underline;
}
</style>