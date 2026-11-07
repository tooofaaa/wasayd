import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useLocale() {
    const { locale, t } = useI18n()

    const isRtl = computed(() => locale.value === 'ar')
    const dir = computed(() => (isRtl.value ? 'rtl' : 'ltr'))

    function toggleLocale() {
        const newLocale = locale.value === 'ar' ? 'en' : 'ar'
        locale.value = newLocale
        document.documentElement.lang = newLocale
        document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr'

        router.post(
            '/locale',
            { locale: newLocale },
            { preserveState: true, preserveScroll: true },
        )
    }

    function setLocale(newLocale: 'ar' | 'en') {
        locale.value = newLocale
        document.documentElement.lang = newLocale
        document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr'
    }

    return { locale, isRtl, dir, t, toggleLocale, setLocale }
}
