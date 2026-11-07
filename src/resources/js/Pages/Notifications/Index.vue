<script setup>
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Bell, CheckCheck, AlertCircle, Package, Calendar, Shield } from 'lucide-vue-next'

const props = defineProps({
  notifications: Object,
  unreadCount: Number
})

const markAsRead = (notificationId) => {
  router.post(route('notifications.read', notificationId))
}

const markAllAsRead = () => {
  router.post(route('notifications.read-all'))
}

const getIcon = (type) => {
  const icons = {
    LowStockNotification: AlertCircle,
    ExpiryAlertNotification: Calendar,
    MaintenanceDueNotification: Calendar,
    ApprovalPendingNotification: Shield,
  }
  return icons[type] || Bell
}
</script>

<template>
  <Head title="Notifications" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Notifications</h1>
          <p class="text-muted-foreground mt-2">
            {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
          </p>
        </div>
        <Button v-if="unreadCount > 0" @click="markAllAsRead" variant="outline">
          <CheckCheck class="w-4 h-4 mr-2" />
          Mark All as Read
        </Button>
      </div>

      <Card v-if="notifications.data.length === 0">
        <CardContent class="py-12 text-center">
          <Bell class="w-12 h-12 mx-auto text-muted-foreground mb-4" />
          <p class="text-lg font-medium">No notifications</p>
          <p class="text-muted-foreground">You're all caught up!</p>
        </CardContent>
      </Card>

      <div v-else class="space-y-3">
        <Card
          v-for="notification in notifications.data"
          :key="notification.id"
          :class="{ 'bg-muted/30': notification.is_read }"
        >
          <CardContent class="pt-6">
            <div class="flex items-start gap-4">
              <component
                :is="getIcon(notification.type)"
                :class="[
                  'w-8 h-8 flex-shrink-0',
                  notification.is_read ? 'text-muted-foreground' : 'text-primary'
                ]"
              />
              <div class="flex-1">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <h3 class="font-semibold">{{ notification.data.title }}</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                      {{ notification.data.message }}
                    </p>
                  </div>
                  <Badge v-if="!notification.is_read" variant="default">New</Badge>
                </div>
                <div class="flex items-center justify-between mt-3">
                  <p class="text-xs text-muted-foreground">{{ notification.created_at }}</p>
                  <Button
                    v-if="!notification.is_read"
                    @click="markAsRead(notification.id)"
                    size="sm"
                    variant="ghost"
                  >
                    Mark as Read
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
