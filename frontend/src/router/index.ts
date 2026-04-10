import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('../views/UsersView.vue'),
    },
    {
      path: '/workout-plans',
      name: 'workout-plans',
      component: () => import('../views/WorkoutPlansView.vue'),
    },
    {
      path: '/workout-plans/:id',
      name: 'plan-detail',
      component: () => import('../views/PlanDetailView.vue'),
    },
  ],
})

export default router
