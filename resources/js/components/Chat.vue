<template>
    <div class="chat">
        <div class="chat__header">
      <span class="chat__header__greetings">
        Hello, {{ userData.userName }}
      </span>
        </div>
        <chat-list :msgs="msgData"></chat-list>
        <chat-form @submitMessage="sendMessage"></chat-form>
    </div>
</template>

<script>
import {mapMutations, mapState} from "vuex";
import axios from 'axios';
import ChatList from "./ChatList.vue";
import ChatForm from "./ChatForm.vue";

export default {
    data() {
        return {
            userData: {
                userImage: '',
                userName: ''
            },
            msgData: [], // Initialize an empty array for storing messages
        };
    },

    components: {
        ChatList,
        ChatForm,
    },
    created() {
        this.userData = {
            userImage: 's',
            userName: 's'
        };
        this.sendBotMessage("Привіт, я бот ZakonOnline. Опишіть свою проблему, а я знайду висновок із списку рішень з правовими позиціями");

    },
    methods: {
        async sendMessage(msg) {
            const username = this.userData.userName;
            const avatar = this.userData.userImage;

            this.msgData.push({
                from: {
                    name: "DevplaCalledMe",
                    avatar: avatar,
                },
                msg,
            });

            // Get the bot's response
            const botResponse = await this.getBotResponse(msg);

            // Send the bot's response
            this.sendBotMessage(botResponse);

            setTimeout(() => {
                const element = document.getElementById("chat__body");
                element.scrollTop = element.scrollHeight;
            }, 0);
        },

        async getBotResponse(userMessage) {
            try {
                const response = await axios.post('/api/bot-response', {
                    message: userMessage,
                });

                return response.data.response;
            } catch (err) {
                console.error(err);
                return "Something went wrong. Please try again.";
            }
        },

        sendBotMessage(msg) {
            this.msgData.push({
                from: {
                    name: "Bot",
                    avatar: "",
                },
                msg,
            });

            setTimeout(() => {
                const element = document.getElementById("chat__body");
                element.scrollTop = element.scrollHeight;
            }, 0);
        },
    },
};
</script>

<style scoped>
.chat {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 80vh; /* Set the height to occupy the full viewport */
    padding-bottom: 50px;

}

.chat__header {
    background: #ffffff;
    box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.05);
    border-radius: 24px 24px 0px 0px;
    padding: 1.8rem;
    font-size: 16px;
    font-weight: 700;
}

.chat__header__greetings {
    color: #292929;
}
</style>
