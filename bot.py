import telebot
from telebot.types import WebAppInfo, ReplyKeyboardMarkup, KeyboardButton

# Токен вашего бота
TOKEN = "8153545853:AAERSnp5uWFARV2emTcioQ8bCw4ggtwQbO0"

# URL вашего веб-приложения
WEBAPP_URL = "http://localhost/fit/mini_app.php"

# Создаем экземпляр бота
bot = telebot.TeleBot(TOKEN)

@bot.message_handler(commands=['start'])
def start(message):
    """Обработчик команды /start"""
    # Создаем клавиатуру с кнопкой для запуска веб-приложения
    keyboard = ReplyKeyboardMarkup(resize_keyboard=True)
    webapp_button = KeyboardButton(
        text="🏋️‍♂️ Открыть Фитнес-клуб",
        web_app=WebAppInfo(url=WEBAPP_URL)
    )
    keyboard.add(webapp_button)
    
    bot.reply_to(
        message,
        "Добро пожаловать в фитнес-клуб! 🏋️‍♂️\n\n"
        "Нажмите кнопку ниже, чтобы открыть приложение:",
        reply_markup=keyboard
    )

def main():
    """Запуск бота"""
    print("Бот запущен. Нажмите Ctrl+C для остановки.")
    bot.infinity_polling()

if __name__ == '__main__':
    main() 