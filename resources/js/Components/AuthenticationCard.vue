<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <slot name="logo" />
        </div>

        <!-- UPDATE 04/04 This if for the registration form layout -->
        <div class="w-full screen-sm: sm:max-w-screen-2xl mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg ">
            <slot name="registerForm"/> 
        </div>
    </div>
</template>
