import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        isAuth: false,
        authUser: null
    }),
    getters: {
      getAuthUser: () => isAuth ? authUser : null,
    },
    actions: {
      setIsAuth(isAuth) {
        this.isAuth = isAuth;
      },
      setAuthUser(authUser) {
        this.authUser = authUser;
      }
    },
  })
