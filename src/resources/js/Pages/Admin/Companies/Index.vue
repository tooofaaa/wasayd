<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Table } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { Select } from '@/Components/ui/select';
import { Dialog } from '@/Components/ui/dialog';
import { DialogContent } from '@/Components/ui/dialog';
import { DialogTrigger } from '@/Components/ui/dialog';
import { ConfirmDialog } from '@/Components/ui/dialog';
import { Link } from '@inertiajs/vue3';

const { data } = usePage();
const companies = data.companies || [];
const filters = data.filters || {};

const openDeleteDialog = ref(false);
const companyToDelete = ref(null);

const openDeleteDialogHandler = (company) => {
    companyToDelete.value = company;
    openDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (companyToDelete.value) {
        window.location.href = `/admin/companies/${companyToDelete.value.id}`;
        openDeleteDialog.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Companies</h1>
                <p class="text-sm text-muted-foreground">Manage tenant companies</p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.companies.create')">
                    <Button>Add Company</Button>
                </Link>
            </div>
        </div>

        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="bg-muted text-sm text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Country</th>
                            <th class="px-4 py-3">Users</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in companies.data" :key="company.id" class="border-b">
                            <td class="px-4 py-3">{{ company.name }}</td>
                            <td class="px-4 py-3">{{ company.code }}</td>
                            <td class="px-4 py-3">{{ company.email || '-' }}</td>
                            <td class="px-4 py-3">{{ company.country }}</td>
                            <td class="px-4 py-3">{{ company.users_count || 0 }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="company.is_active ? 'success' : 'destructive'" class="w-fit">
                                    {{ company.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 flex gap-2">
                                <Link :href="route('admin.companies.edit', company.id)">
                                    <Button variant="outline" size="sm">Edit</Button>
                                </Link>
                                <Button
                                    variant="outline destructive"
                                    size="sm"
                                    @click="openDeleteDialogHandler(company)"
                                >
                                    Delete
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mt-4">
                <div class="flex flex-col sm:flex-row gap-2">
                    <Input
                        placeholder="Search companies..."
                        v-model="filters.search"
                        class="w-full sm:w-64"
                    />
                    <Select
                        v-model="filters.is_active"
                        class="w-full sm:w-64"
                    >
                        <option value="">All Status</option>
                        <option value="true">Active</option>
                        <option value="false">Inactive</option>
                    </Select>
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-2">
                    <Pagination
                        :links="companies.links"
                        :value="companies.current_page"
                        @update:value="page => router.get(companies.links.base_url + '?page=' + page, { preserveState: true })"
                    />
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="openDeleteDialog"
            title="Delete Company"
            description="Are you sure you want to delete this company? This cannot be undone."
            @confirm="confirmDelete"
            @close="openDeleteDialog = false"
        />
    </div>
</template>