<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Calendar" description="Team notes for any day." />
    </x-slot>

    <div
        x-data="calendarApp(@js($monthPayload), @js($notesPayload), @js($categories), @js(route('calendar.month')), @js(route('calendar.notes')), @js(route('calendar.notes.store')))"
        class="space-y-4"
    >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
            <!-- Calendar -->
            <x-ui.card class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-ink-900 dark:text-ink-50" x-text="monthLabel"></h2>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="prevMonth" class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-500 dark:text-ink-400 hover:bg-ink-100 dark:hover:bg-ink-800" title="Previous month">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                        </button>
                        <button type="button" @click="goToday" class="px-3 h-8 rounded-lg text-xs font-semibold text-ink-600 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800">Today</button>
                        <button type="button" @click="nextMonth" class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-500 dark:text-ink-400 hover:bg-ink-100 dark:hover:bg-ink-800" title="Next month">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-1 mb-1">
                    <template x-for="label in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="label">
                        <div class="text-center text-xs font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500 py-1" x-text="label"></div>
                    </template>
                </div>

                <div class="space-y-1" :class="{ 'opacity-50 pointer-events-none': loading }">
                    <template x-for="(week, wIndex) in weeks" :key="wIndex">
                        <div class="grid grid-cols-7 gap-1">
                            <template x-for="day in week" :key="day.date">
                                <button
                                    type="button"
                                    @click="selectDate(day.date)"
                                    x-bind:data-date="day.date"
                                    class="relative aspect-square flex flex-col items-center justify-center gap-0.5 rounded-lg text-sm transition"
                                    :class="dayClasses(day)"
                                >
                                    <span x-text="day.day"></span>
                                    <span class="flex items-center gap-0.5 h-1.5">
                                        <template x-if="day.noteCount === 1">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="day.isSelected ? 'bg-white' : 'bg-brand-500'"></span>
                                        </template>
                                        <template x-if="day.noteCount > 1">
                                            <span class="text-[10px] leading-none px-1 rounded-full font-semibold"
                                                  :class="day.isSelected ? 'bg-white text-brand-700' : 'bg-brand-500 text-white'"
                                                  x-text="day.noteCount"></span>
                                        </template>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </x-ui.card>

            <!-- Daily Notes panel -->
            <x-ui.card class="lg:sticky lg:top-20">
                <div class="flex items-start justify-between gap-2 mb-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-400 dark:text-ink-500">Daily Notes</p>
                        <p class="text-lg font-bold text-ink-900 dark:text-ink-50 truncate" x-text="dateLabel"></p>
                    </div>
                    @can('daily-notes.manage')
                        <x-primary-button type="button" @click="openAddModal" class="shrink-0 !px-3 !py-1.5 !text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Add Note
                        </x-primary-button>
                    @endcan
                </div>

                <div class="space-y-3 max-h-[28rem] overflow-y-auto" :class="{ 'opacity-50 pointer-events-none': loading }">
                    <template x-if="notes.length === 0">
                        <x-ui.empty-state title="No notes for this day" :description="null">
                            <x-slot name="action">
                                <p class="text-xs text-ink-400 dark:text-ink-500 mb-3" x-text="'Add your first note for ' + dateLabel + '.'"></p>
                                @can('daily-notes.manage')
                                    <x-secondary-button type="button" @click="openAddModal">+ Add Note</x-secondary-button>
                                @endcan
                            </x-slot>
                        </x-ui.empty-state>
                    </template>

                    <template x-for="note in notes" :key="note.id">
                        <div class="rounded-xl border border-ink-200/70 dark:border-ink-700/70 p-3.5">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-sm text-ink-900 dark:text-ink-50" x-text="note.title"></p>
                                <span x-show="note.category" x-text="note.category" class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400 ring-1 ring-inset ring-brand-600/20 dark:ring-brand-400/30"></span>
                            </div>
                            <p x-show="note.time_label" x-text="note.time_label" class="text-xs text-ink-400 dark:text-ink-500 mt-0.5"></p>
                            <p x-show="note.content" x-text="note.content" class="text-sm text-ink-600 dark:text-ink-300 mt-2 whitespace-pre-line"></p>

                            @can('daily-notes.manage')
                                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-ink-100 dark:border-ink-800">
                                    <button type="button" @click="openEditModal(note)" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300">Edit</button>
                                    <button type="button" @click="confirmDelete(note)" class="text-xs font-medium text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300">Delete</button>
                                </div>
                            @endcan
                        </div>
                    </template>
                </div>
            </x-ui.card>
        </div>

        <!-- Add / Edit modal -->
        <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
            <div class="fixed inset-0 bg-ink-900/50" @click="closeModal"></div>
            <div x-show="modalOpen" x-transition class="relative mx-auto max-w-lg bg-white dark:bg-ink-900 rounded-xl shadow-xl">
                <form @submit.prevent="saveNote" class="p-6 space-y-4">
                    <h3 class="text-base font-semibold text-ink-900 dark:text-ink-50" x-text="modalMode === 'add' ? 'Add Note' : 'Edit Note'"></h3>

                    <div>
                        <x-input-label value="Title *" />
                        <x-text-input type="text" class="mt-1 block w-full" x-model="form.title" required autofocus />
                        <p x-show="formErrors.title" x-text="formErrors.title && formErrors.title[0]" class="text-sm text-rose-600 dark:text-rose-400 mt-1"></p>
                    </div>

                    <div>
                        <x-input-label value="Description" />
                        <textarea x-model="form.content" rows="3" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        <p x-show="formErrors.content" x-text="formErrors.content && formErrors.content[0]" class="text-sm text-rose-600 dark:text-rose-400 mt-1"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Time" />
                            <input type="time" x-model="form.time" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            <p x-show="formErrors.time" x-text="formErrors.time && formErrors.time[0]" class="text-sm text-rose-600 dark:text-rose-400 mt-1"></p>
                        </div>
                        <div>
                            <x-input-label value="Category" />
                            <select x-model="form.category" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">None</option>
                                <template x-for="option in categories" :key="option">
                                    <option :value="option" x-text="option"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <x-secondary-button type="button" @click="closeModal">Cancel</x-secondary-button>
                        <x-primary-button type="submit" x-bind:disabled="saving">
                            <span x-text="saving ? 'Saving...' : 'Save Note'"></span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete confirmation -->
        <div x-show="deleteTarget" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
            <div class="fixed inset-0 bg-ink-900/50" @click="cancelDelete"></div>
            <div x-show="deleteTarget" x-transition class="relative mx-auto max-w-sm bg-white dark:bg-ink-900 rounded-xl shadow-xl p-6 space-y-4">
                <h3 class="text-base font-semibold text-ink-900 dark:text-ink-50">Delete this note?</h3>
                <p class="text-sm text-ink-500 dark:text-ink-400">
                    "<span x-text="deleteTarget?.title"></span>" will be permanently removed. This can't be undone.
                </p>
                <div class="flex items-center justify-end gap-3">
                    <x-secondary-button type="button" @click="cancelDelete">Cancel</x-secondary-button>
                    <x-danger-button type="button" @click="deleteNote">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calendarApp(initialMonth, initialNotes, categories, monthUrl, notesUrl, storeUrl) {
            return {
                month: initialMonth.month,
                monthLabel: initialMonth.label,
                weeks: initialMonth.weeks,
                selectedDate: initialNotes.date,
                dateLabel: initialNotes.label,
                notes: initialNotes.notes,
                categories: categories,
                loading: false,

                modalOpen: false,
                modalMode: 'add',
                form: { id: null, title: '', content: '', time: '', category: '' },
                formErrors: {},
                saving: false,

                deleteTarget: null,

                csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]').content;
                },

                dayClasses(day) {
                    if (day.isSelected) {
                        return 'bg-brand-600 dark:bg-brand-500 text-white font-semibold';
                    }
                    if (!day.inMonth) {
                        return 'text-ink-300 dark:text-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800';
                    }
                    if (day.isToday) {
                        return 'font-semibold text-brand-600 dark:text-brand-400 ring-1 ring-inset ring-brand-400 dark:ring-brand-600 hover:bg-brand-50 dark:hover:bg-brand-500/10';
                    }
                    return 'text-ink-700 dark:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-800';
                },

                async loadMonth(monthStr) {
                    this.loading = true;
                    try {
                        const res = await fetch(`${monthUrl}?month=${monthStr}&date=${this.selectedDate}`, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        this.month = data.month;
                        this.monthLabel = data.label;
                        this.weeks = data.weeks;
                    } finally {
                        this.loading = false;
                    }
                },

                shiftMonth(delta) {
                    const [y, m] = this.month.split('-').map(Number);
                    const d = new Date(y, m - 1 + delta, 1);
                    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
                },

                async prevMonth() {
                    await this.loadMonth(this.shiftMonth(-1));
                },

                async nextMonth() {
                    await this.loadMonth(this.shiftMonth(1));
                },

                async goToday() {
                    const today = new Date();
                    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                    await this.selectDate(todayStr);
                },

                async selectDate(dateStr) {
                    this.selectedDate = dateStr;
                    this.weeks = this.weeks.map((week) => week.map((day) => ({ ...day, isSelected: day.date === dateStr })));

                    const dateMonth = dateStr.slice(0, 7);
                    if (dateMonth !== this.month) {
                        await this.loadMonth(dateMonth);
                    }

                    await this.loadNotes(dateStr);
                },

                async loadNotes(dateStr) {
                    this.loading = true;
                    try {
                        const res = await fetch(`${notesUrl}?date=${dateStr}`, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        this.dateLabel = data.label;
                        this.notes = data.notes;
                    } finally {
                        this.loading = false;
                    }
                },

                openAddModal() {
                    this.modalMode = 'add';
                    this.form = { id: null, title: '', content: '', time: '', category: '' };
                    this.formErrors = {};
                    this.modalOpen = true;
                },

                openEditModal(note) {
                    this.modalMode = 'edit';
                    this.form = { id: note.id, title: note.title, content: note.content || '', time: note.time || '', category: note.category || '' };
                    this.formErrors = {};
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                async saveNote() {
                    this.saving = true;
                    this.formErrors = {};

                    const url = this.modalMode === 'add' ? storeUrl : `${storeUrl}/${this.form.id}`;
                    const method = this.modalMode === 'add' ? 'POST' : 'PUT';

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: JSON.stringify({
                                date: this.selectedDate,
                                title: this.form.title,
                                content: this.form.content,
                                time: this.form.time,
                                category: this.form.category,
                            }),
                        });

                        if (res.status === 422) {
                            const data = await res.json();
                            this.formErrors = data.errors || {};
                            return;
                        }

                        this.modalOpen = false;
                        await this.loadNotes(this.selectedDate);
                        await this.loadMonth(this.month);
                    } finally {
                        this.saving = false;
                    }
                },

                confirmDelete(note) {
                    this.deleteTarget = note;
                },

                cancelDelete() {
                    this.deleteTarget = null;
                },

                async deleteNote() {
                    if (!this.deleteTarget) {
                        return;
                    }

                    const id = this.deleteTarget.id;
                    await fetch(`${storeUrl}/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                    });

                    this.deleteTarget = null;
                    await this.loadNotes(this.selectedDate);
                    await this.loadMonth(this.month);
                },
            };
        }
    </script>
</x-app-layout>
