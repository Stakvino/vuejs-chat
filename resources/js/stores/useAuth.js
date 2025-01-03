import { defineStore } from 'pinia'
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        isAuth: false,
        isEmailVerified: false,
        authUser: null,
        authCheckError: false,
        authFetchError: false,
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
      async fetchAuthCheck() {
        return axios.get('/api/auth-check')
        .then(response => {
            this.setIsAuth(!!response.data['isAuth']);
            this.authCheckError = false;
        })
        .catch(e => {
            console.log('catch error response', e);
            this.authCheckError = true;
        });
      },
      async fetchAuthUser() {
        return axios.get('/api/auth-user')
            .then(response => {
                const responseData = response['data'];
                if ( responseData['success'] && responseData['user'] ) {
                    const authUser = responseData['user'];
                    this.authUser = authUser;
                    this.isEmailVerified = !( authUser['email_verified_at'] === null );
                    this.authFetchError = false;
                }
                else {
                    // error message
                }
            })
            .catch(e => {
                console.log('catch error response', e);
                this.authFetchError = true;
            })
      },
    },
  })
