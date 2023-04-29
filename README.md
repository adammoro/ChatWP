# ChatWP WordPress Plugin #

A WordPress plugin that displays text from OpenAI's Completions API. Enter your OpenAI API key in the settings and then add as many prompt-specific widget instances as you'd like! Each widget instance can be configured with its own prompt and OpenAI configs.

## Usage ##

Using this plugin is simple. Follow these quick steps to get AI generated text on your WordPress website.

1. Download the folder `chatwp` folder (or the ChatWP.zip file which has that folder in it as well), upload it to your `wp-plugins` folder, and then Activate the ChatWP plugin in the Plugins section.

2. Go to Settings->ChatWP and add your OpenAI API key.

3. Once your API key is saved, go to Appearance->Widgets and look for a widget named "ChatWP Widget". Enter your prompt and any other settings you want to adjust for that widget's specific call to the API. Default values have been provided and work great. Just update the prompt and you're good!.


## OpenAI API Parameters Explained ##

Here's a summary of the parameters passed in a call to the OpenAI API:

1. 'model': Specifies the OpenAI model to use (e.g., 'text-davinci-003').
2. 'prompt': The input text or context for the model to generate a response.
3. 'temperature': Controls the randomness or creativity of the generated text.
4. 'max_tokens': Limits the length of the generated text, measured in tokens.
5. 'frequency_penalty': Penalizes tokens based on their frequency in the training data.
6. 'presence_penalty': Penalizes the model for repeating tokens or phrases.
