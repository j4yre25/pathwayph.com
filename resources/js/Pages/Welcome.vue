<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },

});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50  text-black/50 text-white/50">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]"/>
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:justify-center lg:col-start-2">
                        <svg class="h-12 w-auto text-white lg:h-16 lg:text-[#FF2D20]" viewBox="0 0 62 65" fill="none"></svg>
                    </div>
                    <nav v-if="canLogin" class="flex lg:justify-center lg:col-start-2">
                        
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-black"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-black"
                            >
                                Log in
                            </Link>

<<<<<<< HEAD
                           
=======
                            <!-- REMOVE LINK FOR REGISTER -->
>>>>>>> 6f3cd7f8841184333d13531bc6b3872553462d72
                        </template>
                    </nav>
                </header>

                

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                
                </footer>
            </div>
        </div>
    </div>
</template>
