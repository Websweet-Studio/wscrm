<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Calendar, CheckCircle2, Clock, Edit, Plus, Search, Trash2, LayoutList, CalendarDays, ChevronLeft, ChevronRight, AlertCircle, Circle, ArrowRightCircle, XCircle } from 'lucide-vue-next';
import { computed, ref, watch, onMounted } from 'vue';
import { startOfMonth, endOfMonth, startOfWeek, endOfWeek, eachDayOfInterval, format, isSameMonth, isSameDay, addMonths, subMonths, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';

interface User {
    id: number;
    name: string;
    email: string;
    employee?: {
        department?: string;
    };
}

interface Task {
    id: number;
    title: string;
    description?: string;
    status: 'todo' | 'in_progress' | 'done' | 'cancelled';
    priority: 'low' | 'medium' | 'high';
    due_date?: string;
    assigned_user_id?: number | null;
    assigned_department?: string | null;
    created_by_user_id: number;
    created_at: string;
    updated_at: string;
    assigned_user?: User | null;
    creator?: User | null;
}

interface Props {
    tasks: {
        data: Task[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    departments: string[];
    users: User[];
    userDepartments: Record<number, string>;
    filters?: {
        status?: string;
        assigned_user_id?: number;
        assigned_department?: string;
        view_mode?: 'list' | 'calendar';
        calendar_date?: string;
        scope?: 'all' | 'assigned' | 'created';
    };
    editingTask?: Task | null;
}

const props = defineProps<Props>();
const $page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tasks', href: '/admin/tasks' },
];

const statusFilter = ref(props.filters?.status || '');
const assignedUserId = ref(props.filters?.assigned_user_id || '');
const assignedDepartment = ref(props.filters?.assigned_department || '');
const viewMode = ref(props.filters?.view_mode || 'list');
const currentDate = ref(props.filters?.calendar_date ? parseISO(props.filters.calendar_date) : new Date());
const scopeFilter = ref(props.filters?.scope || 'assigned');
const showCreateModal = ref(false);
const showEditModal = ref(false);

const isCalendarView = computed(() => viewMode.value === 'calendar');

const calendarDays = computed(() => {
    const monthStart = startOfMonth(currentDate.value);
    const monthEnd = endOfMonth(monthStart);
    const startDate = startOfWeek(monthStart, { weekStartsOn: 1 });
    const endDate = endOfWeek(monthEnd, { weekStartsOn: 1 });

    return eachDayOfInterval({
        start: startDate,
        end: endDate,
    });
});

const createForm = useForm({
    title: '',
    description: '',
    status: 'todo' as 'todo' | 'in_progress' | 'done' | 'cancelled',
    priority: 'medium' as 'low' | 'medium' | 'high',
    due_date: '',
    assigned_user_id: '' as number | '' | null,
    assigned_department: '' as string | '',
});

const editForm = useForm({
    id: 0,
    title: '',
    category: '',
    description: '',
    status: 'todo' as 'todo' | 'in_progress' | 'done' | 'cancelled',
    priority: 'medium' as 'low' | 'medium' | 'high',
    due_date: '',
    assigned_user_id: '' as number | '' | null,
    assigned_department: '' as string | '',
});

const handleSearch = () => {
    router.get('/admin/tasks', {
        status: statusFilter.value || undefined,
        category: categoryFilter.value || undefined,
        assigned_user_id: assignedUserId.value || undefined,
        assigned_department: assignedDepartment.value || undefined,
        view_mode: viewMode.value,
        calendar_date: viewMode.value === 'calendar' ? format(currentDate.value, 'yyyy-MM-dd') : undefined,
        scope: scopeFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

const toggleView = () => {
    viewMode.value = viewMode.value === 'list' ? 'calendar' : 'list';
    handleSearch();
};

const prevMonth = () => {
    currentDate.value = subMonths(currentDate.value, 1);
    handleSearch();
};

const nextMonth = () => {
    currentDate.value = addMonths(currentDate.value, 1);
    handleSearch();
};

const getTasksForDay = (date: Date) => {
    return props.tasks.data.filter(task => {
        if (!task.due_date) return false;
        return isSameDay(parseISO(task.due_date), date);
    });
};

const submitCreate = () => {
    // If neither assigned_user_id nor assigned_department provided, backend will default to self
    const payload: any = {
        title: createForm.title,
        description: createForm.description || undefined,
        status: createForm.status,
        priority: createForm.priority,
        due_date: createForm.due_date || undefined,
        assigned_user_id: createForm.assigned_user_id || undefined,
        assigned_department: createForm.assigned_department || undefined,
    };
    createForm.post('/admin/tasks', {
        data: payload,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            createForm.status = 'todo';
            createForm.priority = 'medium';
        },
    });
};

watch(() => createForm.assigned_user_id, (val) => {
    if (typeof val === 'number' && props.userDepartments[val]) {
        createForm.assigned_department = props.userDepartments[val];
    } else if (val === '' || val === null) {
        createForm.assigned_department = '';
    }
});

const openEditModal = (task: Task) => {
    editForm.id = task.id;
    editForm.title = task.title;
    editForm.description = task.description || '';
    editForm.status = task.status;
    editForm.priority = task.priority;
    editForm.due_date = task.due_date || '';
    editForm.assigned_user_id = task.assigned_user_id || '';
    editForm.assigned_department = task.assigned_department || '';
    if (typeof editForm.assigned_user_id === 'number' && props.userDepartments[editForm.assigned_user_id]) {
        editForm.assigned_department = props.userDepartments[editForm.assigned_user_id];
    }
    showEditModal.value = true;
};

onMounted(() => {
    if (props.editingTask) {
        openEditModal(props.editingTask);
    }
});


const submitEdit = () => {
    const payload: any = {
        title: editForm.title,
        category: editForm.category || undefined,
        description: editForm.description || undefined,
        status: editForm.status,
        priority: editForm.priority,
        due_date: editForm.due_date || undefined,
        assigned_user_id: editForm.assigned_user_id || undefined,
        assigned_department: editForm.assigned_department || undefined,
    };
    editForm.patch(`/admin/tasks/${editForm.id}`, {
        data: payload,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

watch(() => editForm.assigned_user_id, (val) => {
    if (typeof val === 'number' && props.userDepartments[val]) {
        editForm.assigned_department = props.userDepartments[val];
    } else if (val === '' || val === null) {
        editForm.assigned_department = '';
    }
});
const statusBadgeClass = (status: Task['status']) => {
    switch (status) {
        case 'todo':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'done':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return '';
    }
};

const priorityBadgeClass = (priority: Task['priority']) => {
    switch (priority) {
        case 'low':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
        case 'medium':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'high':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return '';
    }
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getTaskIcon = (status: Task['status']) => {
    switch (status) {
        case 'done':
            return CheckCircle2;
        case 'in_progress':
            return ArrowRightCircle;
        case 'cancelled':
            return XCircle;
        case 'todo':
        default:
            return Circle;
    }
};
</script>

<template>
    <Head title="Admin - Tasks" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Kelola Tasks</h1>
                    <p class="text-muted-foreground">Buat dan kelola tugas untuk user atau departemen</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button @click="toggleView" variant="outline" class="cursor-pointer">
                        <component :is="isCalendarView ? LayoutList : CalendarDays" class="mr-2 h-4 w-4" />
                        {{ isCalendarView ? 'Tampilkan List' : 'Tampilkan Kalender' }}
                    </Button>
                    <Button @click="showCreateModal = true" class="cursor-pointer">
                        <Plus class="mr-2 h-4 w-4" />
                        Buat Task
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent>
                    <div class="mb-3 flex items-center gap-2">
                        <Button
                            :variant="scopeFilter === 'all' ? 'default' : 'outline'"
                            size="sm"
                            class="cursor-pointer"
                            @click="scopeFilter = 'all'; handleSearch();"
                        >Semua</Button>
                        <Button
                            :variant="scopeFilter === 'assigned' ? 'default' : 'outline'"
                            size="sm"
                            class="cursor-pointer"
                            @click="scopeFilter = 'assigned'; handleSearch();"
                        >Ditugaskan ke Saya</Button>
                        <Button
                            :variant="scopeFilter === 'created' ? 'default' : 'outline'"
                            size="sm"
                            class="cursor-pointer"
                            @click="scopeFilter = 'created'; handleSearch();"
                        >Dibuat oleh Saya</Button>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                        <div>
                            <select id="statusFilter" v-model="statusFilter" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                <option value="">Semua Status</option>
                                <option value="todo">Todo</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <select id="categoryFilter" v-model="categoryFilter" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                        <div>
                            <select id="assignedUserId" v-model="assignedUserId" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                <option value="">Semua User</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                        <div>
                            <select id="assignedDepartment" v-model="assignedDepartment" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                <option value="">Semua Dept</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <Button @click="handleSearch" class="w-full cursor-pointer">Cari</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Calendar View -->
            <Card v-if="isCalendarView" class="h-full border-none shadow-none bg-transparent">
                <div class="mb-4 flex items-center justify-between bg-white p-4 rounded-lg shadow dark:bg-gray-800">
                    <div class="flex items-center space-x-4">
                        <Button variant="outline" size="icon" @click="prevMonth" class="h-8 w-8 cursor-pointer">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <h2 class="text-xl font-bold">{{ format(currentDate, 'MMMM yyyy', { locale: id }) }}</h2>
                        <Button variant="outline" size="icon" @click="nextMonth" class="h-8 w-8 cursor-pointer">
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ tasks.data.length }} tasks
                    </div>
                </div>
                
                <div class="rounded-lg border bg-white shadow dark:border-gray-700 dark:bg-gray-900">
                    <div class="grid grid-cols-7 border-b dark:border-gray-700">
                        <div v-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Ming']" :key="day" class="py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ day }}
                        </div>
                    </div>
                    <div class="grid grid-cols-7 bg-gray-200 gap-px dark:bg-gray-700">
                        <div
                            v-for="date in calendarDays"
                            :key="date.toString()"
                            :class="[
                                'min-h-[120px] bg-white p-2 dark:bg-gray-900 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800',
                                !isSameMonth(date, currentDate) ? 'bg-gray-50/50 text-gray-400 dark:bg-gray-900/50 dark:text-gray-600' : ''
                            ]"
                        >
                            <div class="mb-2 flex justify-between items-start">
                                <span :class="['flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium', isSameDay(date, new Date()) ? 'bg-blue-600 text-white' : '']">
                                    {{ format(date, 'd') }}
                                </span>
                            </div>
                            <div class="space-y-1">
                                <div
                                    v-for="task in getTasksForDay(date)"
                                    :key="task.id"
                                    class="group relative cursor-pointer truncate rounded px-1.5 py-1 text-xs font-medium transition-all hover:ring-2 hover:ring-blue-500 hover:z-10"
                                    :class="[
                                        (task.status === 'todo' && task.assigned_user_id == null) ? 'ring-2 ring-amber-500 dark:ring-amber-400' : '',
                                        task.priority === 'high' ? 'bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/30 dark:text-red-300 dark:border-red-900/50' :
                                        task.priority === 'medium' ? 'bg-yellow-50 text-yellow-700 border border-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-900/50' :
                                        'bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-900/50',
                                        task.status === 'done' ? 'line-through opacity-60 grayscale' : ''
                                    ]"
                                    @click="openEditModal(task)"
                                    :title="task.title"
                                >
                                    <div class="flex items-center gap-1">
                                        <component :is="getTaskIcon(task.status)" class="h-3 w-3 shrink-0" />
                                        <span class="truncate">{{ task.title }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Tasks List -->
            <Card v-else>
                <CardHeader>
                    <CardTitle class="text-base">Daftar Tasks</CardTitle>
                    <CardDescription>Tugas yang ditugaskan ke Anda atau yang Anda buat</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="tasks.data.length === 0" class="py-12 text-center text-muted-foreground">
                        <Search class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada task</h3>
                        <p class="mt-1 text-sm">Buat task baru untuk memulai.</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="task in tasks.data"
                            :key="task.id"
                            class="rounded-lg border p-4 dark:border-gray-700"
                            :class="[(task.status === 'todo' && task.assigned_user_id == null) ? 'ring-2 ring-amber-500 dark:ring-amber-400' : '']"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold">{{ task.title }}</h3>
                                    <p v-if="task.description" class="mt-1 text-sm text-muted-foreground">
                                        {{ task.description }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                        <span :class="['rounded px-2 py-1', statusBadgeClass(task.status)]">{{ task.status }}</span>
                                        <span :class="['rounded px-2 py-1', priorityBadgeClass(task.priority)]">{{ task.priority }}</span>
                                        <span v-if="task.category" class="rounded bg-purple-100 px-2 py-1 text-purple-800 dark:bg-purple-900 dark:text-purple-300">{{ task.category }}</span>
                                        <span v-if="task.due_date" class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            <Calendar class="h-3 w-3" /> {{ formatDate(task.due_date) }}
                                        </span>
                                        <span v-if="task.assigned_user" class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            <Clock class="h-3 w-3" /> {{ task.assigned_user.name }}
                                        </span>
                                        <span
                                            v-if="task.assigned_user?.employee?.department"
                                            class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                        >
                                            <Clock class="h-3 w-3" /> Departemen: {{ task.assigned_user.employee.department }}
                                        </span>
                                        <span v-else-if="task.assigned_department" class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            <Clock class="h-3 w-3" /> Departemen: {{ task.assigned_department }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <template v-if="task.created_by_user_id === ($page.props as any).auth.user.id">
                                        <Button size="sm" variant="outline" class="cursor-pointer" @click="openEditModal(task)">
                                            <Edit class="h-3.5 w-3.5" />
                                        </Button>
                                    </template>
                                    <template v-if="task.status === 'in_progress'">
                                        <Button size="sm" variant="outline" class="cursor-pointer bg-green-600 text-white hover:bg-green-700" @click="router.patch(`/admin/tasks/${task.id}`, { status: 'done' })">
                                            <CheckCircle2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </template>
                                    <template v-else-if="!task.status || task.status === 'todo'">
                                        <Button size="sm" variant="outline" class="cursor-pointer bg-blue-600 text-white hover:bg-blue-700" @click="router.patch(`/admin/tasks/${task.id}`, { status: 'in_progress' })">
                                            <ArrowRightCircle class="h-3.5 w-3.5" />
                                        </Button>
                                    </template>
                                    <template v-if="task.created_by_user_id === ($page.props as any).auth.user.id">
                                        <Button size="sm" variant="outline" class="cursor-pointer" @click="router.delete(`/admin/tasks/${task.id}`)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tasks.links && tasks.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex items-center space-x-2">
                            <template v-for="link in tasks.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'rounded-xl px-4 py-2 text-sm font-medium transition-all',
                                        link.active
                                            ? 'bg-blue-600 text-white shadow-lg'
                                            : 'border border-gray-200 bg-white text-gray-700 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    :class="['cursor-not-allowed rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-500 opacity-50 dark:bg-gray-800']"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </CardContent>
            </Card>

            <!-- Create Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
                <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Buat Task</h3>
                    <div class="space-y-3">
                        <div>
                            <Label for="title">Judul</Label>
                            <Input id="title" v-model="createForm.title" placeholder="Judul task" />
                        </div>
                        <div>
                            <Label for="category">Kategori</Label>
                            <Input id="category" v-model="createForm.category" placeholder="Kategori task (opsional)" />
                        </div>
                        <div>
                            <Label for="description">Deskripsi</Label>
                            <textarea id="description" v-model="createForm.description" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white" rows="3" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <Label for="status">Status</Label>
                                <select id="status" v-model="createForm.status" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="todo">Todo</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="done">Done</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <Label for="priority">Prioritas</Label>
                                <select id="priority" v-model="createForm.priority" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <Label for="due_date">Jatuh Tempo</Label>
                            <DatePicker id="due_date" v-model="createForm.due_date" placeholder="Pilih tanggal jatuh tempo" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <Label for="assigned_user_id">Assign ke User</Label>
                                <select id="assigned_user_id" v-model="createForm.assigned_user_id" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="">— Pilih user —</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                            </div>
                            <div>
                                <Label for="assigned_department">Assign ke Departemen</Label>
                                <select id="assigned_department" v-model="createForm.assigned_department" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="">— Pilih departemen —</option>
                                    <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                                </select>
                                <p class="mt-1 text-xs text-muted-foreground">Jika keduanya kosong, otomatis assign ke diri sendiri.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-2">
                            <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer">Batal</Button>
                            <Button type="button" @click="submitCreate" class="cursor-pointer">Simpan</Button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
                <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Edit Task</h3>
                    <div class="space-y-3">
                        <div>
                            <Label for="edit_title">Judul</Label>
                            <Input id="edit_title" v-model="editForm.title" placeholder="Judul task" />
                        </div>
                        <div>
                            <Label for="edit_category">Kategori</Label>
                            <div class="relative">
                                <Input id="edit_category" v-model="editForm.category" placeholder="Kategori task (opsional)" list="edit-category-list" />
                                <datalist id="edit-category-list">
                                    <option v-for="cat in categories" :key="cat" :value="cat" />
                                </datalist>
                            </div>
                        </div>
                        <div>
                            <Label for="edit_description">Deskripsi</Label>
                            <textarea id="edit_description" v-model="editForm.description" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white" rows="3" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <Label for="edit_status">Status</Label>
                                <select id="edit_status" v-model="editForm.status" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="todo">Todo</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="done">Done</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <Label for="edit_priority">Prioritas</Label>
                                <select id="edit_priority" v-model="editForm.priority" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <Label for="edit_due_date">Jatuh Tempo</Label>
                            <DatePicker id="edit_due_date" v-model="editForm.due_date" placeholder="Pilih tanggal jatuh tempo" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <Label for="edit_assigned_user_id">Assign ke User</Label>
                                <select id="edit_assigned_user_id" v-model="editForm.assigned_user_id" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="">— Pilih user —</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                            </div>
                            <div>
                                <Label for="edit_assigned_department">Assign ke Departemen</Label>
                                <select id="edit_assigned_department" v-model="editForm.assigned_department" class="mt-1 w-full rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="">— Pilih departemen —</option>
                                    <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-2">
                            <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer">Batal</Button>
                            <Button type="button" @click="submitEdit" class="cursor-pointer">Simpan</Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
