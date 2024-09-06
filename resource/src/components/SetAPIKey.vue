<template lang="">
    <v-card elevation="0">
        <v-card-title>Set Gemini API Key</v-card-title>
        <v-card-text>
            <form @submit.prevent="submit">
                <v-text-field label="API Key" v-model="apiKey" variant="outlined"></v-text-field>
                <v-btn
                type="submit"
                >
                    submit
                </v-btn>
            </form>
        </v-card-text>
    </v-card>
    <v-snackbar
      v-model="snackbar"
    >
      {{ snackbar_message }}

      <template v-slot:actions>
        <v-btn
          color="pink"
          variant="text"
          @click="snackbar = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
</template>
<script setup>
    import { ref } from 'vue';
    import Rest from '../helpers/Rest';
    const apiKey = ref('');
    const snackbar = ref(false)
    const snackbar_message = ref('')

    const submit = async () => {
        console.log(this);
        try {
            const response = await Rest.post('set-gemini-api-key', {
                apiKey: apiKey.value
            })
            .then((response) => {
                snackbar.value=true
                snackbar_message.value=response.message
            });
            
        } catch (error) {
            console.error('Error:', error);
        }
    };
</script>
<style lang="">
    
</style>