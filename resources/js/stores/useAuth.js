import { defineStore } from 'pinia'
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        isAuth: false,
        authUser: null
    }),
    getters: {

    },
    actions: {
      setIsAuth(isAuth) {
        this.isAuth = isAuth;
      },
      setAuthUser(authUser) {
        this.authUser = authUser;
      },
      fetchAuthUser(successCallback) {
        axios.get('/api/auth-user')
            .then(response => {
                const responseData = response['data'];
                if ( responseData['success'] && responseData['user'] ) {
                    const authUser = responseData['user'];
                    this.authUser = authUser;
                    if (successCallback) {
                        successCallback(authUser);
                    }
                }
                else {
                    // error message
                }
            })
            .catch(e => console.log('catch error response', e))
      },
    },
  })
