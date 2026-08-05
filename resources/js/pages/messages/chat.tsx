import { Head, useForm, Link } from '@inertiajs/react';
import { ArrowLeft, Send } from 'lucide-react';
import { useState, useRef, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { AppLayout } from '@/components/app-shell';
import { Textarea } from '@/components/ui/textarea';

interface Recipient {
    id: number;
    name: string;
    profile_photo_url: string | null;
}

interface Message {
    id: number;
    message: string;
    created_at: string;
    is_own: boolean;
    sender: {
        id: number;
        name: string;
        profile_photo_url: string | null;
    };
}

interface Props {
    recipient: Recipient;
    messages: Message[];
    conversations: {
        user: {
            id: number;
            name: string;
            profile_photo_url: string | null;
        };
        last_message: {
            id: number;
            message: string;
            created_at: string;
            is_own: boolean;
        } | null;
        unread_count: number;
        updated_at: string | null;
    }[];
}

export default function MessageChat({ recipient, messages, conversations }: Props) {
    const [newMessage, setNewMessage] = useState('');
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const { post, processing } = useForm({
        message: '',
    });

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const handleSend = (e: React.FormEvent) => {
        e.preventDefault();

        if (!newMessage.trim()) {
return;
}

        post(`/messages/${recipient.id}`, {
            data: { message: newMessage },
            onSuccess: () => {
                setNewMessage('');
            },
        });
    };

    return (
        <AppLayout>
            <Head title={`Chat with ${recipient.name}`} />

            <div className="flex h-[calc(100vh-8rem)] gap-6">
                {/* Conversations Sidebar */}
                <div className="hidden lg:block w-80 border-r overflow-y-auto">
                    <div className="p-4 border-b">
                        <h2 className="font-semibold">Conversations</h2>
                    </div>
                    <div className="divide-y">
                        {conversations.map((conv) => (
                            <Link
                                key={conv.user.id}
                                href={`/messages/${conv.user.id}`}
                                className={`p-4 flex items-center gap-3 hover:bg-muted/50 transition-colors ${
                                    conv.user.id === recipient.id ? 'bg-muted' : ''
                                }`}
                            >
                                <div className="relative">
                                    {conv.user.profile_photo_url ? (
                                        <img
                                            src={conv.user.profile_photo_url}
                                            alt={conv.user.name}
                                            className="w-10 h-10 rounded-full"
                                        />
                                    ) : (
                                        <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span className="font-medium">
                                                {conv.user.name.charAt(0)}
                                            </span>
                                        </div>
                                    )}
                                    {conv.unread_count > 0 && (
                                        <span className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                            {conv.unread_count}
                                        </span>
                                    )}
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="font-medium truncate">{conv.user.name}</p>
                                    {conv.last_message && (
                                        <p className="text-sm text-muted-foreground truncate">
                                            {conv.last_message.is_own ? 'You: ' : ''}
                                            {conv.last_message.message}
                                        </p>
                                    )}
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>

                {/* Chat Area */}
                <div className="flex-1 flex flex-col">
                    {/* Chat Header */}
                    <div className="flex items-center gap-4 p-4 border-b">
                        <Link href="/messages" className="lg:hidden">
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-5 w-5" />
                            </Button>
                        </Link>
                        {recipient.profile_photo_url ? (
                            <img
                                src={recipient.profile_photo_url}
                                alt={recipient.name}
                                className="w-10 h-10 rounded-full"
                            />
                        ) : (
                            <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <span className="font-medium">
                                    {recipient.name.charAt(0)}
                                </span>
                            </div>
                        )}
                        <div>
                            <Link href={`/profile/${recipient.id}`} className="font-medium hover:underline">
                                {recipient.name}
                            </Link>
                        </div>
                    </div>

                    {/* Messages */}
                    <div className="flex-1 overflow-y-auto p-4 space-y-4">
                        {messages.length === 0 ? (
                            <p className="text-center text-muted-foreground py-8">
                                No messages yet. Say hello!
                            </p>
                        ) : (
                            messages.map((message) => (
                                <div
                                    key={message.id}
                                    className={`flex gap-3 ${
                                        message.is_own ? 'flex-row-reverse' : ''
                                    }`}
                                >
                                    {!message.is_own && (
                                        message.sender.profile_photo_url ? (
                                            <img
                                                src={message.sender.profile_photo_url}
                                                alt={message.sender.name}
                                                className="w-8 h-8 rounded-full flex-shrink-0"
                                            />
                                        ) : (
                                            <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                                <span className="text-sm font-medium">
                                                    {message.sender.name.charAt(0)}
                                                </span>
                                            </div>
                                        )
                                    )}
                                    <div className={`max-w-[70%] ${message.is_own ? 'text-right' : ''}`}>
                                        <div
                                            className={`inline-block px-4 py-2 rounded-lg ${
                                                message.is_own
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'bg-muted'
                                            }`}
                                        >
                                            <p>{message.message}</p>
                                        </div>
                                        <p className="text-xs text-muted-foreground mt-1">
                                            {new Date(message.created_at).toLocaleString()}
                                        </p>
                                    </div>
                                </div>
                            ))
                        )}
                        <div ref={messagesEndRef} />
                    </div>

                    {/* Message Input */}
                    <form onSubmit={handleSend} className="p-4 border-t">
                        <div className="flex gap-2">
                            <Textarea
                                value={newMessage}
                                onChange={(e) => setNewMessage(e.target.value)}
                                placeholder="Type a message..."
                                rows={1}
                                className="resize-none"
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' && !e.shiftKey) {
                                        e.preventDefault();
                                        handleSend(e);
                                    }
                                }}
                            />
                            <Button type="submit" disabled={processing || !newMessage.trim()}>
                                <Send className="h-4 w-4" />
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}