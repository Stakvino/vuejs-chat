import { defineStore } from 'pinia'
import { useRouter } from 'vue-router';

export const useAppStore = defineStore('app', {
    state: () => ({
        navIsReady: false,
        ContentIsReady: false,
        currentRouteName: useRouter().currentRoute.value.name
    }),
    getters: {
        getPageIsLoading() { return !this.navIsReady || !this.ContentIsReady ; }
    },
    actions: {
      setNavIsReady(navIsReady) {
        this.navIsReady = navIsReady;
      },
      setContentIsReady(ContentIsReady) {
        this.ContentIsReady = ContentIsReady;
      },
      setCurrentRouteName(currentRouteName) {
        this.currentRouteName = currentRouteName;
      }
    },
  })
