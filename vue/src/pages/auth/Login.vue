<script setup>
import { reactive, ref } from 'vue';

let form = reactive({
  email: '',
  password: ''
});

const errors = ref({});

function login() {
  axios.post('http://localhost:8081/user/login', form)
      .then((response) => {
          errors.value = {};
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
        <h1>Login</h1>

        <form @submit.prevent="login">
            <input type="email" placeholder="Your email" v-model="form.email"/>
            <p v-if="errors.email">{{ errors.email[0] }}</p>
            <input type="password" placeholder="Your password" v-model="form.password"/>
            <p v-if="errors.password">{{ errors.password[0] }}</p>
            <div class="form-footer">
                <button type="submit">Login</button>
                <router-link to="/register">Click here to register</router-link>
            </div>
        </form>
    </auth-layout>
</template>

<style scoped></style>