import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/workout-plans',
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('../users/UsersView.vue'),
    },
    {
      path: '/workout-plans',
      name: 'workout-plans',
      component: () => import('../workout-plans/WorkoutPlansView.vue'),
    },
  ],
})

export default router
