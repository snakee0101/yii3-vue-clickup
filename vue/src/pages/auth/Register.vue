<script setup>
import { reactive, ref } from 'vue';

let form = reactive({
  name: '',
  email: '',
  password: ''
});

const errors = ref({});

function register() {
  axios.post('http://localhost:8081/user/register', form)
      .then((response) => {
        errors.value = {};
        console.log(response);
        localStorage.setItem('access_token', response.data.access_token);
        window.location = 'http://localhost:5173';
      })
      .catch((error) => {
        errors.value = error.response.data.errors;
      })
}
</script>

<template>
    <auth-layout>
        <h1>Register</h1>

        <form @submit.prevent="register">
            <input type="text" placeholder="Your name" v-model="form.name"/>
            <p v-if="errors.name">{{ errors.name[0] }}</p>

            <input type="email" placeholder="Your email" v-model="form.email"/>
            <p v-if="errors.email">{{ errors.email[0] }}</p>

            <input type="password" placeholder="Your password" v-model="form.password"/>
            <p v-if="errors.password">{{ errors.password[0] }}</p>

            <div class="form-footer">
                <button type="submit">Register</button>
                <router-link to="/login">Click here to login</router-link>
            </div>
        </form>
    </auth-layout>
</template>

<style scoped></style>