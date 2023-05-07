package com.app.streamsphere;

import com.app.streamsphere.Kafka.MyKafkaConsumer;
import org.springframework.boot.CommandLineRunner;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class StreamsphereApplication implements CommandLineRunner {

    public static void main(String[] args) {
        SpringApplication.run(StreamsphereApplication.class, args);
    }
    private final MyKafkaConsumer myKafkaConsumer;

    public StreamsphereApplication(MyKafkaConsumer myKafkaConsumer) {
        this.myKafkaConsumer = myKafkaConsumer;
    }

    @Override
    public void run(String... args) throws Exception {
        myKafkaConsumer.consumeMessages();
    }
}
