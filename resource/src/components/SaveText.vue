<template>
    <v-card elevation="0">
        <v-row no-gutters class="mt-5">
            <v-col cols="8"></v-col>
            <v-col cols="4" class="button" @click="saveDoc">
                Save As DOC
            </v-col>
        </v-row>
    </v-card>
</template>
<script setup>
    import { defineProps } from 'vue'


    const props = defineProps({
        content: {
            type: String,
            required: true
        }
    })


    const saveDoc = () => {
        const doc = new Document({
            sections: [
                {
                    properties: {},
                    children: [
                        new Paragraph({
                            children: [new TextRun(props.content)],
                        }),
                    ],
                },
            ],
        })

        Packer.toBlob(doc).then((blob) => {
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = 'document.docx'
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
        })
    }

    

</script>
<style>
    
</style>