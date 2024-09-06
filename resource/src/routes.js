import AskGemini from './components/AskGemini.vue';
// import SetAPIKey from './components/SetAPIKey.vue';

export var routes = [
    {
        path: '/',
        name: 'ask-gemini',
        component: AskGemini,
        meta: {
            active: 'ask-gemini'
        }
    },
    // {
    //     path: '/set-gemini-api-key',
    //     name: 'set-gemini-api-key',
    //     component: SetAPIKey,
    //     meta: {
    //         active: 'set-gemini-api-key'
    //     }
    // },
];