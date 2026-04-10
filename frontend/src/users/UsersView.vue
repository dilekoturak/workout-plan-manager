<script setup lang="ts">
import { ref } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { toast } from 'vue-sonner'
import { Plus, Pencil, Trash2, Loader2 } from 'lucide-vue-next'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
  TableEmpty,
} from '@/components/ui/table'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import { userApi } from './userApi'
import UserSheet from './UserSheet.vue'
import type { User } from './types'

const queryClient = useQueryClient()

// ── Sheet state ────────────────────────────────────────────
const sheetOpen = ref(false)
const editingUser = ref<User | null>(null)

function openCreate() {
  editingUser.value = null
  sheetOpen.value = true
}

function openEdit(user: User) {
  editingUser.value = user
  sheetOpen.value = true
}

// ── Delete dialog state ────────────────────────────────────
const deleteDialogOpen = ref(false)
const userToDelete = ref<User | null>(null)

function openDelete(user: User) {
  userToDelete.value = user
  deleteDialogOpen.value = true
}

// ── Queries & Mutations ────────────────────────────────────
const { data: users, isLoading, isError } = useQuery({
  queryKey: ['users'],
  queryFn: userApi.getAll,
})

const { mutate: deleteUser, isPending: isDeleting } = useMutation({
  mutationFn: (id: string) => userApi.delete(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] })
    toast.success('User deleted.')
    deleteDialogOpen.value = false
  },
  onError: () => toast.error('Failed to delete user.'),
})
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Users</h1>
        <p class="text-sm text-muted-foreground">Manage application users.</p>
      </div>
      <Button @click="openCreate">
        <Plus class="mr-2 h-4 w-4" />
        Add User
      </Button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-16">
      <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
    </div>

    <!-- Error -->
    <div v-else-if="isError" class="rounded-md border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive">
      Failed to load users. Is the backend running?
    </div>

    <!-- Table -->
    <div v-else class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>First Name</TableHead>
            <TableHead>Last Name</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Created</TableHead>
            <TableHead class="w-24 text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableEmpty v-if="!users?.length" :colspan="5">
            No users yet. Click "Add User" to create one.
          </TableEmpty>
          <TableRow v-for="user in users" :key="user.id">
            <TableCell class="font-medium">{{ user.firstName }}</TableCell>
            <TableCell>{{ user.lastName }}</TableCell>
            <TableCell>{{ user.email }}</TableCell>
            <TableCell class="text-muted-foreground">
              {{ new Date(user.createdAt).toLocaleDateString() }}
            </TableCell>
            <TableCell class="text-right">
              <div class="flex items-center justify-end gap-1">
                <Button variant="ghost" size="icon" @click="openEdit(user)">
                  <Pencil class="h-4 w-4" />
                </Button>
                <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" @click="openDelete(user)">
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>

  <!-- Add / Edit Sheet -->
  <UserSheet v-model:open="sheetOpen" :user="editingUser" />

  <!-- Delete Confirmation -->
  <AlertDialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Delete user?</AlertDialogTitle>
        <AlertDialogDescription>
          This will permanently delete
          <span class="font-medium">{{ userToDelete?.firstName }} {{ userToDelete?.lastName }}</span>
          and all their workout plan assignments. This action cannot be undone.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Cancel</AlertDialogCancel>
        <AlertDialogAction
          class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
          :disabled="isDeleting"
          @click="userToDelete && deleteUser(userToDelete.id)"
        >
          <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
          Delete
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
