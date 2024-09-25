# # Use a PHP base image
# FROM php:8.1-cli

# # Install any necessary dependencies (like curl for cURL support)
# RUN apt-get update && apt-get install -y \
#     curl \
#     git \
#     && docker-php-ext-install mysqli

# # Set the working directory
# WORKDIR /var/www/html
# # Clone the repository
# RUN git clone https://github.com/Chlakhna/kobo.git .

# # Install Composer if needed (optional)
# # COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# # Set any environment variables (optional)
# # ENV VARIABLE_NAME=value


# # Run your PHP script (replace script.php with your actual script)
# CMD ["php", "database.php"]



# Use a PHP base image
FROM php:8.1-cli

# Install necessary dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    git \
    && docker-php-ext-install mysqli

# Set the working directory
WORKDIR /var/www/html

# Clone the repository
RUN git clone https://github.com/Chlakhna/kobo.git .

# Install Composer and dependencies (if your project requires it)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# RUN composer install # Uncomment if you have a composer.json file

# Set environment variables (optional)
# ENV VARIABLE_NAME=value

# Run your PHP script
CMD ["php", "database.php"]
