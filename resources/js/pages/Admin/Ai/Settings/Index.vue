<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { Cpu, KeyRound, Link2, Save } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    settings: {
        endpoint: string;
        api_key: string;
        api_key_set: boolean;
        model: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    endpoint: props.settings.endpoint,
    api_key: '',
    model: props.settings.model,
});

const saving = ref(false);

const save = () => {
    saving.value = true;
    form.patch('/admin/ai/settings', {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
            form.api_key = '';
        },
    });
};
</script>

<template>
    <Head title="Pengaturan AI" />

    <AppLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-medium" style="font-family: Georgia, serif;">Pengaturan AI</h1>
                <p class="text-muted-foreground">
                    Konfigurasi endpoint, API key, dan model AI yang dipakai oleh AI Agent & chatbot customer service.
                </p>
            </div>

            <Card class="rounded-2xl shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2 font-serif font-medium tracking-tight">
                        <Cpu class="h-4 w-4" />
                        <span>Koneksi AI</span>
                    </CardTitle>
                    <CardDescription class="leading-relaxed">
                        Atur endpoint, model, dan API key yang dipakai AI Agent & chatbot customer service.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <Label for="endpoint">
                                    <Link2 class="mr-1 inline h-3.5 w-3.5" />
                                    Endpoint
                                </Label>
                                <Input
                                    id="endpoint"
                                    v-model="form.endpoint"
                                    required
                                    placeholder="https://api.openai.com/v1"
                                    class="mt-1"
                                />
                                <p v-if="form.errors.endpoint" class="mt-1 text-sm text-destructive">{{ form.errors.endpoint }}</p>
                            </div>

                            <div>
                                <Label for="model">Model</Label>
                                <Input
                                    id="model"
                                    v-model="form.model"
                                    required
                                    placeholder="gpt-4o-mini"
                                    class="mt-1"
                                />
                                <p v-if="form.errors.model" class="mt-1 text-sm text-destructive">{{ form.errors.model }}</p>
                            </div>

                            <div>
                                <Label for="api_key">
                                    <KeyRound class="mr-1 inline h-3.5 w-3.5" />
                                    API Key
                                </Label>
                                <Input
                                    id="api_key"
                                    v-model="form.api_key"
                                    type="password"
                                    :placeholder="settings.api_key_set ? '******** (biarkan kosong untuk mempertahankan)' : 'sk-...'"
                                    class="mt-1"
                                />
                                <p v-if="form.errors.api_key" class="mt-1 text-sm text-destructive">{{ form.errors.api_key }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Button type="submit" :disabled="saving" class="cursor-pointer">
                                <Save class="mr-2 h-4 w-4" />
                                {{ saving ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                            <span v-if="settings.api_key_set" class="text-xs text-muted-foreground">
                                API key sudah tersimpan & terenkripsi.
                            </span>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
