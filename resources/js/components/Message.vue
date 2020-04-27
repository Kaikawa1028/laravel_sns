<template>
    <div>
        <div v-for="(message, index) in messages" :key="index">
            <div class="d-flex flex-row mb-3" v-if="message.user_id != authUser.id">
                <a href="#" class="text-dark">
                    <i class="fas fa-user-circle fa-3x mr-1"></i>
                </a>
                <div>
                    <a href="#" class="text-dark">
                        <div class="font-weight-bold">{{ opponentUser.name }}</div>
                    </a>
                    <div class="font-weight-lighter">{{ message.text }}</div>
                </div>
            </div>
            <div class="d-flex flex-row-reverse mb-3" v-if="message.user_id == authUser.id">
                <a href="#" class="text-dark">
                    <i class="fas fa-user-circle fa-3x mr-1"></i>
                </a>
                <div>
                    <a href="#" class="text-dark">
                        <div class="font-weight-bold">{{ authUser.name }}</div>
                    </a>
                    <div class="font-weight-lighter">{{ message.text }}</div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <textarea name="text" v-model="text" required class="form-control" rows="4" placeholder="本文"></textarea>
            </div>
            <button @click="postMessage" class="btn btn-primary">送信する</button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Message",
        props: {
            messageUrl: {
                type: String,
                default: "",
            },
            initialMessages: {
                type: Object,
                default: ""
            },
            authUser: {
                type: Object,
                default: ""
            },
            opponentUser: {
                type: Object,
                default: ""
            }
        },
        data() {
            return {
                messages: this.initialMessages.messages,
                csrf: document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),

                text: "",
                apiToken: document
                    .querySelector('meta[name="api-token"]')
                    .getAttribute("content"),
            }
        },
        mounted() {
            window.Echo.private('post-comment-channel.3')
                .listen('PostMessage', res => {
                    this.messages.push(res.message);
                });
        },
        methods: {
            postMessage() {
                var message = {
                    '_token' : this.csrf,
                    'room_id' : this.initialMessages.id,
                    'user_id' : this.authUser.id,
                    'text'   : this.text
                }

                this.updateMessage(message);

                axios.post(this.messageUrl, message, {
                    headers: {
                        'Authorization' : 'Bearer '+ this.apiToken,
                    }
                }).then(res =>  {
                    this.text = "";
                });
            },
            updateMessage(message) {
                if(this.messages == null){
                    this.messages = [];
                }
                this.messages.push(message);
            }
        }
    }
</script>

<style scoped>

</style>