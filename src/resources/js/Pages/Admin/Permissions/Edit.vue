<script setup>
import { useForm } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';

const { data } = usePage();
const permission = data.permission || {};

const form = useForm({
    name: permission.name,
    guard_name: permission.guard_name || 'web',
});

const submit = () => {
    form.put(route('admin.permissions.update', permission.name), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Edit Permission</h1>

        <form @submit="submit">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="col-span-2">
                    <Label for="name">Permission Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        name="name"
                        placeholder="e.g., users.create"
                        :class="{ 'border-destructive': form.errors.name }"
                    />
                    <p class="text-xs text-destructive" v-if="form.errors.name">{{ form.errors.name }}</p>
                </div>

                <div>
                    <Label for="guard_name">Guard Name</Label>
                    <Select id="guard_name" v-model="form.guard_name" name="guard_name">
                        <option value="web">Web</option>
                        <option value="api">API</option>
                    </select>
                </div>

                <div class="flex items-end gap-2 pt-4">
                    <Button type="button" as="a" :href="route('admin.permissions.index')">
                        Cancel
                    </Button>
                    <Button type="submit">Update Permission</Button>
                </div>
            </div>
        </form>
    </div>
</template>