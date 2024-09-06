<template>
    <v-card elevation="0">
        <v-tabs
        v-model="tab"
        >
            <v-tab value="one">Text Generator</v-tab>
            <v-tab value="two">Document Processing</v-tab>
            <v-tab value="three">Image Generator</v-tab>
        </v-tabs>

        <v-card-text>
            <v-tabs-window v-model="tab">
                <v-tabs-window-item value="one">
                    <form @submit.prevent="submit" class="pa-4">
                        <v-textarea v-model="prompt.value.value" :error-messages="prompt.errorMessage.value" label="Prompt" variant="outlined"></v-textarea>

                        <v-btn
                        type="submit"
                        >
                        submit
                        </v-btn>
                    </form>

                    <v-card elevation="0" class="pa-4">
                        <Editor
                            api-key="qfru4tyer9im95v0l19urccf5nxvs9dkurx7bev2dufl4w3w"
                            :init="{
                                toolbar: 'undo redo | formatselect | bold italic backcolor | \
                                    alignleft aligncenter alignright alignjustify | \
                                    bullist numlist outdent indent | removeformat | help',
                                height: 300,
                            }"
                            initial-value="Ask Me Anything!"
                            v-model="editorContent"
                        />
                        <SaveText :content="editorContent"/>
                    </v-card>
                </v-tabs-window-item>

                <v-tabs-window-item value="two">
                    <form @submit.prevent="submitFile" class="pa-4">
                        <v-file-input v-model="docFile" label="File input" variant="outlined" class="custom-file-input"></v-file-input>

                        <v-btn
                        class="me-4"
                        type="submit"
                        >
                        submit
                        </v-btn>
                    </form>

                    <v-card elevation="0" class="pa-4">
                        <Editor
                            api-key="qfru4tyer9im95v0l19urccf5nxvs9dkurx7bev2dufl4w3w"
                            :init="{
                                toolbar: 'undo redo | formatselect | bold italic backcolor | \
                                    alignleft aligncenter alignright alignjustify | \
                                    bullist numlist outdent indent | removeformat | help',
                                height: 300,
                            }"
                            initial-value="Ask Me Anything!"
                            v-model="docContent"
                        />
                    </v-card>
                </v-tabs-window-item>

                <v-tabs-window-item value="three">
                Three
                </v-tabs-window-item>
            </v-tabs-window>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { ref } from 'vue'
    import Editor from '@tinymce/tinymce-vue'
    import { useField, useForm } from 'vee-validate'
    import { GoogleGenerativeAI } from '@google/generative-ai'
    import { marked } from 'marked'
    import SaveText from './SaveText.vue'




    

    const VITE_GOOGLE_AI_STUDIO_API_KEY = 'AIzaSyAozeqMWq2ORFdOBGJIxRXqSbxt1LWZckk'

    const genAI = new GoogleGenerativeAI(VITE_GOOGLE_AI_STUDIO_API_KEY)
    const model = genAI.getGenerativeModel({ model: 'gemini-1.5-flash' })

    const { handleSubmit, handleReset } = useForm({
        validationSchema: {
            prompt (value) {
                if (value?.length >= 0) return true

                return 'Please give a promt!'
            },
        },
    })

    const prompt = useField('prompt')
    const editorContent = ref('') 
    const docContent = ref('')
    const docFile = ref('');

    const submit = handleSubmit(async (values) => {
        // console.log(values.prompt)
        const result = await model.generateContent(values.prompt);
        const response = await result.response;
        const markdownText = await response.text();
        editorContent.value = marked(markdownText);
    })



    const submitFile = async () => {
        
        if (!docFile.value) {
            console.error('No file selected')
            return
        }
        const mimeType = docFile.value.type;
        console.log(mimeType);

    }

    const tab = ref(0)
</script>
<style>
.custom-file-input.v-input--center-affix .v-input__prepend, .v-input--center-affix .v-input__append{
  display: none !important;
}
</style>