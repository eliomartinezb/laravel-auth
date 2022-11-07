import { createWebHistory, createRouter } from 'vue-router'

//const ForgotPassword = () => import('./views/ForgotPassword.vue')

const routes =  [
    {
        path: "/",
        name: "home",
        component: () => import("@/views/home.vue"),
        meta: {
            title: 'HomeView'
        }
    },

]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
