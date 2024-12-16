<script setup>
    import { RouterLink } from 'vue-router';
    import { Button } from 'primevue';
</script>

<template>
    <div id="hero">
        <div class="md:px-14 pb-14 pt-3 md:py-10">
            <div class="md:mt-3 mx-3 sm:mx-8">
                <h1 class="my-3 sm:my-5 text-2xl sm:text-5xl font-light">Vuejs Chat</h1>
                <h2 class="mb-3 sm:my-5 text-2xl sm:text-5xl font-bold">A Chat app made with Laravel and Vuejs</h2>
                <p class="mb-7 sm:my-5 text-lg md:text-2xl font-light">In order to use this application you will have to register an account using your own personal email.</p>
                <div>
                    <RouterLink to="/register">
                        <Button severity="secondary" class="mr-3 !py-1 !px-3 sm:!py-2 sm:!px-5" raised>Get Started</Button>
                    </RouterLink>
                </div>
                <div class="flex justify-end">
                    <img src="/images/home-hero.png" alt="home hero section image">
                </div>
            </div>
        </div>
    </div>

    <div class="tools-section py-6 px-6 lg:px-20 mt-8 mx-0 lg:mx-20">
        <div class="grid grid-cols-12 gap-4 justify-center">
            <div class="col-span-12 text-center mt-20 mb-6">
                <h4 class="text-4xl">Tools used to make this app</h4>
            </div>
            <div class="grid sm:grid-cols-3 gap-8 col-span-12 p-0 lg:pr-8 lg:pb-8 mt-6 lg:mt-0">

                <div class="item shadow-lg border">
                    <div class="p-4 bg-surface-0 dark:bg-surface-900 h-full">
                        <div class="flex flex-col justify-start items-start">
                            <div class="rounded-md shadow-md bg-yellow-200 p-1">
                                <img src="images/homepage/laravel.png" width="45" alt="laravel logo">
                            </div>
                            <div>
                                <h4 class="text-lg font-bold my-1">Laravel</h4>
                                <p>Laravel 11 was used for the backend of this app</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="item shadow-lg border">
                    <div class="p-4 bg-surface-0 dark:bg-surface-900 h-full">
                        <div class="flex flex-col justify-start items-start">
                            <div class="rounded-md shadow-md bg-pink-200 p-1">
                                <img src="images/homepage/vuejs.webp" width="45" alt="laravel logo">
                            </div>
                            <div>
                                <h4 class="text-lg font-bold my-1">Vuejs</h4>
                                <p>Vuejs 4 was used for the frontend of this app</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item shadow-lg border">
                    <div class="p-4 bg-surface-0 dark:bg-surface-900 h-full">
                        <div class="flex flex-col justify-start items-start">
                            <div class="rounded-md shadow-md bg-blue-200 p-1">
                                <img src="images/homepage/primevue.png" width="45" alt="laravel logo">
                            </div>
                            <div>
                                <h4 class="text-lg font-bold my-1">Primevue</h4>
                                <p>Primvue 4 for the UI components of the frontend</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
