<script setup>
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';

const { data } = usePage();
const companies = data.companies || [];

const form = useForm({
    name: '',
    name_ar: '',
    code: '',
    email: '',
    phone: '',
    address: '',
    tax_number: '',
    license_number: '',
    country: 'SA',
    currency: 'SAR',
    timezone: 'Asia/Riyadh',
    locale: 'ar',
    settings: null,
    is_active: true,
});

const submit = () => {
    form.post(route('admin.companies.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Create Company</h1>

        <form @submit="submit">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="col-span-2">
                    <Label for="name">Company Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        name="name"
                        placeholder="Company Name"
                        :class="{ 'border-destructive': form.errors.name }"
                    />
                    <p class="text-xs text-destructive" v-if="form.errors.name">{{ form.errors.name }}</p>
                </div>

                <div class="col-span-2">
                    <Label for="name_ar">Company Name (Arabic)</Label>
                    <Input
                        id="name_ar"
                        v-model="form.name_ar"
                        name="name_ar"
                        placeholder="اسم الشركة بالعربية"
                    />
                </div>

                <div>
                    <Label for="code">Company Code</Label>
                    <Input
                        id="code"
                        v-model="form.code"
                        name="code"
                        placeholder="e.g., ABC001"
                    />
                    <p class="text-xs text-destructive" v-if="form.errors.code">{{ form.errors.code }}</p>
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        name="email"
                        placeholder="company@example.com"
                    />
                    <p class="text-xs text-destructive" v-if="form.errors.email">{{ form.errors.email }}</p>
                </div>

                <div>
                    <Label for="phone">Phone</Label>
                    <Input
                        id="phone"
                        v-model="form.phone"
                        name="phone"
                        placeholder="+966 50 123 4567"
                    />
                </div>

                <div class="col-span-2">
                    <Label for="address">Address</Label>
                    <Input
                        id="address"
                        v-model="form.address"
                        name="address"
                        placeholder="Company Address"
                    />
                </div>

                <div>
                    <Label for="tax_number">Tax Number</Label>
                    <Input
                        id="tax_number"
                        v-model="form.tax_number"
                        name="tax_number"
                        placeholder="Tax/VAT Number"
                    />
                </div>

                <div>
                    <Label for="license_number">License Number</Label>
                    <Input
                        id="license_number"
                        v-model="form.license_number"
                        name="license_number"
                        placeholder="Business License Number"
                    />
                </div>

                <div>
                    <Label for="country">Country</Label>
                    <Select id="country" v-model="form.country" name="country">
                        <option value="SA">Saudi Arabia</option>
                        <option value="AE">United Arab Emirates</option>
                        <option value="EG">Egypt</option>
                        <option value="QA">Qatar</option>
                        <option value="KW">Kuwait</option>
                        <option value="BH">Bahrain</option>
                        <option value="OM">Oman</option>
                        <option value="JO">Jordan</option>
                    </select>
                </div>

                <div>
                    <Label for="currency">Currency</Label>
                    <Select id="currency" v-model="form.currency" name="currency">
                        <option value="SAR">Saudi Riyal (SAR)</option>
                        <option value="AED">UAE Dirham (AED)</option>
                        <option value="EGP">Egyptian Pound (EGP)</option>
                        <option value="QAR">Qatari Riyal (QAR)</option>
                        <option value="KWD">Kuwaiti Dinar (KWD)</option>
                        <option value="BHD">Bahraini Dinar (BHD)</option>
                        <option value="OMR">Omani Rial (OMR)</option>
                        <option value="JOD">Jordanian Dinar (JOD)</option>
                    </select>
                </div>

                <div>
                    <Label for="timezone">Timezone</Label>
                    <Select id="timezone" v-model="form.timezone" name="timezone">
                        <option value="Asia/Riyadh">Asia/Riyadh</option>
                        <option value="Asia/Dubai">Asia/Dubai</option>
                        <option value="Africa/Cairo">Africa/Cairo</option>
                        <option value="Asia/Qatar">Asia/Qatar</option>
                        <option value="Asia/Kuwait">Asia/Kuwait</option>
                        <option value="Asia/Bahrain">Asia/Bahrain</option>
                        <option value="Asia/Muscat">Asia/Muscat</option>
                        <option value="Asia/Amman">Asia/Amman</option>
                    </select>
                </div>

                <div>
                    <Label for="locale">Locale</Label>
                    <Select id="locale" v-model="form.locale" name="locale">
                        <option value="ar">Arabic (ar)</option>
                        <option value="en">English (en)</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <Label for="settings">Settings (JSON)</Label>
                    <Input
                        id="settings"
                        v-model="form.settings"
                        name="settings"
                        placeholder='{"key": "value"}'
                    />
                    <p class="text-xs text-muted-foreground">Optional JSON configuration</p>
                </div>

                <div class="flex items-end gap-2 pt-4">
                    <Button type="button" as="a" :href="route('admin.companies.index')">
                        Cancel
                    </Button>
                    <Button type="submit">Create Company</Button>
                </div>
            </div>
        </form>
    </div>
</template>