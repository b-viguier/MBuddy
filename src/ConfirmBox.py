import ui


def ask(question):
    popup = ui.load_view()
    choice = False

    def on_ok(sender):
        nonlocal choice, popup
        choice = True
        popup.close()

    def on_cancel(sender):
        nonlocal choice, popup
        choice = False
        popup.close()

    popup['ok_button'].action = on_ok
    popup['cancel_button'].action = on_cancel
    popup['question_label'].text = question

    popup.present('sheet')
    popup.wait_modal()

    return choice


if __name__ == '__main__':
    print(ask("Is it a question?"))
