<script>
export default {
    data() {
        return {
            hrProfile: {
                name: '',
                email: '',
                phone: '',
            },
        };
    },
    methods: {
        updateProfile() {
            // Logic to handle profile update (e.g., API call)
            console.log('Profile updated:', this.hrProfile);
        },
    },
};
</script>

<template>
    <div class="hr-profile">
        <h1>HR Profile</h1>
        <form @submit.prevent="updateProfile">
            <div>
                <label for="name">Name:</label>
                <input
                    type="text"
                    id="name"
                    v-model="hrProfile.name"
                    placeholder="Enter your name"
                />
            </div>
            <div>
                <label for="email">Email:</label>
                <input
                    type="email"
                    id="email"
                    v-model="hrProfile.email"
                    placeholder="Enter your email"
                />
            </div>
            <div>
                <label for="phone">Phone:</label>
                <input
                    type="tel"
                    id="phone"
                    v-model="hrProfile.phone"
                    placeholder="Enter your phone number"
                />
            </div>
            <button type="submit">Update Profile</button>
        </form>
    </div>
</template>

<style scoped>
.hr-profile {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
}
.hr-profile h1 {
    text-align: center;
    margin-bottom: 20px;
}
.hr-profile form div {
    margin-bottom: 15px;
}
.hr-profile label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.hr-profile input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.hr-profile button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.hr-profile button:hover {
    background-color: #0056b3;
}
</style>