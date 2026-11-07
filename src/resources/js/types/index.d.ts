import type { User } from './models'

export interface PageProps {
  auth: {
    user: User
  }
  locale: string
  flash: {
    success?: string
    error?: string
    warning?: string
    info?: string
  }
  ziggy: {
    url: string
    port: number | null
    defaults: Record<string, string>
    routes: Record<string, { uri: string; methods: string[] }>
  }
}

declare module '@inertiajs/vue3' {
  interface InertiaPageProps extends PageProps {}
}
