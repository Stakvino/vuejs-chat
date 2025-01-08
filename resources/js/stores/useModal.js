import { defineStore } from 'pinia'

export const useModalStore = defineStore('modal', {
    state: () => ({
        // editProfileModalIsVisible: false,
        // showProfileModalIsVisible: false,
        isProfileModalVisible: false,
        isChannelModalVisible: false,
    }),
    getters: {

    },
    actions: {
      setProfileModal(isVisible) {
        this.isProfileModalVisible = isVisible;
      },
      setChannelModal(isVisible) {
        this.isChannelModalVisible = isVisible;
      },
    },
  })
