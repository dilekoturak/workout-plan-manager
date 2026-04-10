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
      component: () => import('../users/UsersView.vue'),
    },
    {
      path: '/workout-plans',
      name: 'workout-plans',
      component: () => import('../workout-plans/WorkoutPlansView.vue'),
    },
    {
      path: '/workout-plans/:id',
      name: 'plan-detail',
      component: () => import('../workout-plans/PlanDetailView.vue'),
    },
  ],
})

export default router
