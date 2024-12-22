import { defineStore } from 'pinia'

export const useModalStore = defineStore('modal', {
    state: () => ({
        editProfileModalIsVisible: false,
        showProfileModalIsVisible: false,
    }),
    getters: {

    },
    actions: {
      setEditProfileModal(editProfileModalIsVisible) {
        this.editProfileModalIsVisible = editProfileModalIsVisible;
      },
      setShowProfileModal(showProfileModalIsVisible) {
        this.showProfileModalIsVisible = showProfileModalIsVisible;
      },
    },
  })
