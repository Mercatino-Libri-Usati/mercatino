import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const routes = [
  {
    path: '/report',
    name: 'report',
    component: () => import('@/views/ReportView.vue'),
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/DashboardView.vue'),
  },
  {
    path: '/cassa',
    name: 'cassa',
    component: () => import('../views/CassaView.vue'),
  },
  {
    path: '/catalogo',
    name: 'catalogo',
    component: () => import('../views/CatalogoView.vue'),
  },
  {
    path: '/magazzino',
    name: 'magazzino',
    component: () => import('@/views/MagazzinoView.vue'),
  },
  {
    path: '/prenotati',
    name: 'prenotati',
    component: () => import('@/views/PrenotatiView.vue'),
  },
  {
    path: '/restituzione',
    name: 'restituzione',
    component: () => import('@/views/RestituzioneView.vue'),
  },
  {
    path: '/ricevute',
    name: 'ricevute',
    component: () => import('@/views/RicevuteView.vue'),
  },
  {
    path: '/ritiro',
    name: 'ritiro',
    component: () => import('@/views/RitiroView.vue'),
  },
  {
    path: '/utenti',
    name: 'utenti',
    component: () => import('@/views/UtentiView.vue'),
  },
  {
    path: '/vendita',
    name: 'vendita',
    component: () => import('@/views/VenditaView.vue'),
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'error',
    component: () => import('@/views/Error404View.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
  },
  {
    path: '/registrazione',
    name: 'registrazione',
    component: () => import('@/views/RegisterView.vue'),
  },
  {
    path: '/richiedi-link-password',
    name: 'richiedi-link-password',
    component: () => import('@/views/RequestLinkVue.vue'),
  },
  {
    path: '/imposta-password',
    name: 'imposta-password',
    component: () => import('@/views/ImpostaPasswordView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  const loginPages = ['login', 'richiedi-link-password', 'registrazione', 'imposta-password']
  const authRequired = !loginPages.includes(to.name)

  if (!authStore.isAuthenticated && authRequired) {
    await authStore.whoami()
  }

  if (authRequired && !authStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  } else if (loginPages.includes(to.name) && authStore.isAuthenticated) {
    return { name: 'dashboard' }
  } else {
    return true
  }
})

export default router
