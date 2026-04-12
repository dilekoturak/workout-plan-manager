<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
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
import type { User } from './types'

const props = defineProps<{
  open: boolean
  user?: User | null
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const isEditing = computed(() => !!props.user)

// ── Zod schema ───────────────────────────────────────────────
const userSchema = z.object({
  firstName: z.string().min(1, 'First name is required.'),
  lastName: z.string().min(1, 'Last name is required.'),
  email: z.string().min(1, 'Email is required.').email('Please enter a valid email.'),
})

// ── Form ─────────────────────────────────────────────────────
const { handleSubmit, errors, resetForm, defineField, setErrors } = useForm({
  validationSchema: toTypedSchema(userSchema),
})

const [firstName, firstNameAttrs] = defineField('firstName')
const [lastName, lastNameAttrs] = defineField('lastName')
const [email, emailAttrs] = defineField('email')

function populateForm(u: User | null | undefined) {
  resetForm({
    values: u
      ? { firstName: u.firstName, lastName: u.lastName, email: u.email }
      : { firstName: '', lastName: '', email: '' },
  })
}

// Reset form every time the sheet opens
watch(() => props.open, (open) => {
  if (open) populateForm(props.user)
})

// Initial populate when sheet is closed
watch(() => props.user, (u) => {
  if (!props.open) populateForm(u)
}, { immediate: true })

// ── Mutation ─────────────────────────────────────────────────
const queryClient = useQueryClient()

const { mutate: save, isPending } = useMutation({
  mutationFn: (values: z.infer<typeof userSchema>) =>
    isEditing.value
      ? userApi.update(props.user!.id, values)
      : userApi.create(values),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] })
    toast.success(isEditing.value ? 'User updated.' : 'User created.')
    emit('update:open', false)
  },
  onError: (err: any) => {
    if (err?.response?.status === 422) {
      const raw = err.response.data?.errors ?? {}
      setErrors(Object.fromEntries(
        Object.entries(raw).map(([k, v]) => [k, (v as string[]).join(', ')]),
      ))
    } else if (err?.response?.status === 409) {
      setErrors({ email: 'This email is already in use.' })
    } else {
      toast.error('Something went wrong.')
    }
  },
})

const submit = handleSubmit((values) => save(values))
</script>

<template>
  <Sheet :open="open" @update:open="emit('update:open', $event)">
    <SheetContent>
      <SheetHeader>
        <SheetTitle>{{ isEditing ? 'Edit User' : 'Add User' }}</SheetTitle>
        <SheetDescription>
          {{ isEditing ? 'Update the user details below.' : 'Fill in the details to create a new user.' }}
        </SheetDescription>
      </SheetHeader>

      <form class="flex flex-col gap-5 px-4 py-6" @submit.prevent="submit">
        <div class="flex flex-col gap-1.5">
          <Label for="firstName">First Name</Label>
          <Input id="firstName" v-model="firstName" v-bind="firstNameAttrs" placeholder="Alice" />
          <p v-if="errors.firstName" class="text-sm text-destructive">{{ errors.firstName }}</p>
        </div>

        <div class="flex flex-col gap-1.5">
          <Label for="lastName">Last Name</Label>
          <Input id="lastName" v-model="lastName" v-bind="lastNameAttrs" placeholder="Smith" />
          <p v-if="errors.lastName" class="text-sm text-destructive">{{ errors.lastName }}</p>
        </div>

        <div class="flex flex-col gap-1.5">
          <Label for="email">Email</Label>
          <Input id="email" v-model="email" v-bind="emailAttrs" type="email" placeholder="alice@example.com" />
          <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
        </div>

        <SheetFooter class="pt-2">
          <Button type="submit" :disabled="isPending" class="w-full">
            <Loader2 v-if="isPending" class="mr-2 h-4 w-4 animate-spin" />
            {{ isEditing ? 'Save Changes' : 'Create User' }}
          </Button>
        </SheetFooter>
      </form>
    </SheetContent>
  </Sheet>
</template>
