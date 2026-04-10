<script setup lang="ts">
import { ref, watch } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { toast } from 'vue-sonner'
import { Loader2 } from 'lucide-vue-next'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
  SheetFooter,
} from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { userApi } from './userApi'
import type { User, CreateUserPayload } from './types'

const props = defineProps<{
  open: boolean
  user?: User | null
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const queryClient = useQueryClient()

const form = ref({ firstName: '', lastName: '', email: '' })
const errors = ref<Record<string, string>>({})

// Populate form when editing
watch(
  () => props.user,
  (u) => {
    if (u) {
      form.value = { firstName: u.firstName, lastName: u.lastName, email: u.email }
    } else {
      form.value = { firstName: '', lastName: '', email: '' }
    }
    errors.value = {}
  },
  { immediate: true },
)

const isEditing = () => !!props.user

const { mutate: save, isPending } = useMutation({
  mutationFn: (payload: CreateUserPayload) =>
    isEditing()
      ? userApi.update(props.user!.id, payload)
      : userApi.create(payload),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] })
    toast.success(isEditing() ? 'User updated.' : 'User created.')
    emit('update:open', false)
  },
  onError: (err: any) => {
    if (err?.response?.status === 422) {
      const raw = err.response.data?.errors ?? {}
      errors.value = Object.fromEntries(
        Object.entries(raw).map(([k, v]) => [k, (v as string[]).join(', ')]),
      )
    } else if (err?.response?.status === 409) {
      errors.value = { email: 'This email is already in use.' }
    } else {
      toast.error('Something went wrong.')
    }
  },
})

function submit() {
  errors.value = {}
  const { firstName, lastName, email } = form.value
  const localErrors: Record<string, string> = {}
  if (!firstName.trim()) localErrors.firstName = 'First name is required.'
  if (!lastName.trim()) localErrors.lastName = 'Last name is required.'
  if (!email.trim()) localErrors.email = 'Email is required.'
  if (Object.keys(localErrors).length) {
    errors.value = localErrors
    return
  }
  save({ firstName: firstName.trim(), lastName: lastName.trim(), email: email.trim() })
}
</script>

<template>
  <Sheet :open="open" @update:open="emit('update:open', $event)">
    <SheetContent>
      <SheetHeader>
        <SheetTitle>{{ isEditing() ? 'Edit User' : 'Add User' }}</SheetTitle>
        <SheetDescription>
          {{ isEditing() ? 'Update the user details below.' : 'Fill in the details to create a new user.' }}
        </SheetDescription>
      </SheetHeader>

      <form class="flex flex-col gap-5 px-4 py-6" @submit.prevent="submit">
        <div class="flex flex-col gap-1.5">
          <Label for="firstName">First Name</Label>
          <Input id="firstName" v-model="form.firstName" placeholder="Alice" />
          <p v-if="errors.firstName" class="text-sm text-destructive">{{ errors.firstName }}</p>
        </div>

        <div class="flex flex-col gap-1.5">
          <Label for="lastName">Last Name</Label>
          <Input id="lastName" v-model="form.lastName" placeholder="Smith" />
          <p v-if="errors.lastName" class="text-sm text-destructive">{{ errors.lastName }}</p>
        </div>

        <div class="flex flex-col gap-1.5">
          <Label for="email">Email</Label>
          <Input id="email" v-model="form.email" type="email" placeholder="alice@example.com" />
          <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
        </div>

        <SheetFooter class="pt-2">
          <Button type="submit" :disabled="isPending" class="w-full">
            <Loader2 v-if="isPending" class="mr-2 h-4 w-4 animate-spin" />
            {{ isEditing() ? 'Save Changes' : 'Create User' }}
          </Button>
        </SheetFooter>
      </form>
    </SheetContent>
  </Sheet>
</template>
