import { defineStore } from 'pinia'
import { useRouter } from 'vue-router';

export const useAppStore = defineStore('app', {
    state: () => ({
        navIsReady: false,
        ContentIsReady: false,
        currentRouteName: useRouter().currentRoute.value.name,
        isMobileScreen: window.innerWidth < maxMobileScreenSize
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

const maxMobileScreenSize = 750;
window.addEventListener('resize', e => {
    return window.innerWidth < maxMobileScreenSize
    ? useAppStore().isMobileScreen = true
    : useAppStore().isMobileScreen = false;
})
