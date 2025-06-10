FROM node:18-alpine

# Set working directory
WORKDIR /app

# Install dependencies
RUN apk add --no-cache git

# Expose port
EXPOSE 3000

# Start development server
CMD ["sh", "-c", "npm install && npm run dev"]